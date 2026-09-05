<?php

namespace App\Services\Translation;

use App\Contracts\TranslationProviderInterface;
use App\Models\Language;
use App\Models\Package;
use App\Models\Itinerary;
use App\Models\PackageHighlight;
use App\Models\PackageInclusion;
use App\Models\NileCruiseCabin;
use App\Models\NileCruiseItineraryDay;
use App\Services\Translation\DTOs\TranslationOptions;
use App\Services\Translation\DTOs\TranslationResult;
use App\Services\Translation\DTOs\TranslationUnit;
use App\Services\Translation\Providers\GeminiTranslationProvider;
use App\Services\Translation\Providers\DeepSeekTranslationProvider;
use App\Services\Translation\Schemas\PackageTranslationSchema;
use App\Support\LocaleNormalizer;
use App\Support\RateLimitedLogger;
use Illuminate\Support\Facades\Log;

class AiTranslationService
{
    protected GeminiTranslationProvider $geminiProvider;
    protected DeepSeekTranslationProvider $deepseekProvider;
    protected TranslationValidator $validator;
    protected TranslationCacheService $cacheService;
    protected PackageTranslationSchema $packageSchema;

    public function __construct(
        GeminiTranslationProvider $geminiProvider,
        DeepSeekTranslationProvider $deepseekProvider,
        TranslationValidator $validator,
        TranslationCacheService $cacheService,
        PackageTranslationSchema $packageSchema,
        protected LocaleNormalizer $localeNormalizer,
        protected RateLimitedLogger $rateLimitedLogger
    ) {
        $this->geminiProvider = $geminiProvider;
        $this->deepseekProvider = $deepseekProvider;
        $this->validator = $validator;
        $this->cacheService = $cacheService;
        $this->packageSchema = $packageSchema;
    }

    /**
     * Get active target languages from the database.
     */
    public function getActiveLanguages(): array
    {
        if (class_exists(Language::class)) {
            $codes = Language::query()
                ->where('is_active', true)
                ->pluck('code')
                ->map(fn($c) => $this->localeNormalizer->normalize((string) $c))
                ->toArray();

            if (!empty($codes)) {
                return array_values(array_unique($codes));
            }
        }

        return ['en', 'ar'];
    }

    /**
     * Translate a single TranslationUnit using Gemini -> DeepSeek Fallback flow.
     */
    public function translateUnit(TranslationUnit $unit, ?TranslationOptions $options = null): TranslationResult
    {
        $unit->sourceLanguage = $this->localeNormalizer->normalize($unit->sourceLanguage);
        $unit->targetLanguage = $this->localeNormalizer->normalize($unit->targetLanguage);

        if (!(bool) config('translation.ai_enabled', true)) {
            return TranslationResult::failure('disabled', 'disabled', 'AI translation is disabled.');
        }

        $cachedText = $this->cacheService->getCached($unit);
        if ($cachedText !== null && !empty(trim($cachedText))) {
            return new TranslationResult(
                translatedText: $cachedText,
                provider: 'cache',
                model: 'cache',
                isSuccess: true,
                errorMessage: null
            );
        }

        $options = $options ?? new TranslationOptions(structuredType: $unit->structuredType);
        $lastFailure = null;

        foreach ($this->providersInOrder() as $provider) {
            $result = $provider->translate(
                $unit->sourceText,
                $unit->sourceLanguage,
                $unit->targetLanguage,
                $options
            );

            if ($result->isSuccess) {
                if (!$this->validator->validate(
                    $unit->sourceText,
                    $result->translatedText,
                    $unit->sourceLanguage,
                    $unit->targetLanguage,
                    $unit->structuredType
                )) {
                    $lastFailure = TranslationResult::failure(
                        $result->provider,
                        $result->model,
                        'Translation response validation failed.'
                    );
                    continue;
                }

                $result->translatedText = $this->validator->cleanMarkdownCodeBlocks($result->translatedText);
                $this->cacheService->storeCache($unit, $result);
                $this->cacheService->logUsage(
                    $unit,
                    $result,
                    $result->provider === config('translation.provider') ? 'success' : 'fallback'
                );

                return $result;
            }

            $lastFailure = $result;
        }

        $lastFailure ??= TranslationResult::failure('unavailable', 'unavailable', 'No translation provider is available.');
        $this->cacheService->logUsage($unit, $lastFailure, 'failed');

        return $lastFailure;
    }

    /**
     * Translate keyed strings in bounded JSON batches. Cached strings never
     * reach a provider and a provider failure returns the original values.
     *
     * @param array<string, string> $items
     * @return array<string, string>
     */
    public function translateBatch(array $items, string $targetLanguage, string $sourceLanguage = 'en'): array
    {
        $sourceLanguage = $this->localeNormalizer->normalize($sourceLanguage);
        $targetLanguage = $this->localeNormalizer->normalize($targetLanguage);
        $result = [];
        $pending = [];

        foreach ($items as $key => $text) {
            $text = trim((string) $text);
            $result[$key] = $text;

            if ($text === '' || $sourceLanguage === $targetLanguage) {
                continue;
            }

            $unit = new TranslationUnit('batch', 0, (string) $key, $sourceLanguage, $targetLanguage, $text);
            $cached = $this->cacheService->getCached($unit);

            if ($cached !== null) {
                $result[$key] = $cached;
            } else {
                $pending[$key] = $unit;
            }
        }

        if (!(bool) config('translation.ai_enabled', true) || $pending === []) {
            return $result;
        }

        foreach ($this->chunkUnits($pending) as $chunk) {
            $chunkKeys = array_keys($chunk);
            $sourceValues = array_values(array_map(fn (TranslationUnit $unit): string => $unit->sourceText, $chunk));
            $sourceJson = json_encode($sourceValues, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if (!is_string($sourceJson)) {
                continue;
            }

            $options = new TranslationOptions(structuredType: 'json_array', temperature: 0.1);
            $batchResult = null;

            foreach ($this->providersInOrder() as $provider) {
                $candidate = $provider->translate($sourceJson, $sourceLanguage, $targetLanguage, $options);
                if (!$candidate->isSuccess) {
                    continue;
                }

                $clean = $this->validator->cleanMarkdownCodeBlocks($candidate->translatedText);
                if (!$this->validator->validateJsonArray($sourceJson, $clean)) {
                    continue;
                }

                $decoded = json_decode($clean, true);
                if (!is_array($decoded)) {
                    continue;
                }

                $batchResult = [$candidate, $decoded];
                break;
            }

            if ($batchResult === null) {
                continue;
            }

            [$providerResult, $translations] = $batchResult;

            foreach ($chunkKeys as $index => $key) {
                $unit = $chunk[$key];
                $translated = $translations[$index] ?? null;
                if (!is_string($translated)
                    || !$this->validator->validate($unit->sourceText, $translated, $sourceLanguage, $targetLanguage)) {
                    continue;
                }

                $translated = $this->validator->cleanMarkdownCodeBlocks($translated);
                $itemResult = new TranslationResult(
                    $translated,
                    $providerResult->provider,
                    $providerResult->model,
                    true,
                    null,
                    durationMs: $providerResult->durationMs
                );
                $result[$key] = $translated;
                $this->cacheService->storeCache($unit, $itemResult);
            }
        }

        return $result;
    }

    /** @return array<int, TranslationProviderInterface> */
    private function providersInOrder(): array
    {
        $available = [
            'gemini' => $this->geminiProvider,
            'google' => $this->geminiProvider,
            'deepseek' => $this->deepseekProvider,
        ];

        $names = array_unique([
            strtolower((string) config('translation.provider', 'gemini')),
            strtolower((string) config('translation.fallback_provider', 'deepseek')),
        ]);

        return array_values(array_filter(array_map(
            static fn (string $name): ?TranslationProviderInterface => $available[$name] ?? null,
            $names
        )));
    }

    /**
     * @param array<string, TranslationUnit> $units
     * @return array<int, array<string, TranslationUnit>>
     */
    private function chunkUnits(array $units): array
    {
        $maxItems = (int) config('translation.batch_size', 30);
        $maxChars = (int) config('translation.max_chars_per_request', 8000);
        $chunks = [];
        $chunk = [];
        $chars = 0;

        foreach ($units as $key => $unit) {
            $length = mb_strlen($unit->sourceText);
            if ($chunk !== [] && (count($chunk) >= $maxItems || $chars + $length > $maxChars)) {
                $chunks[] = $chunk;
                $chunk = [];
                $chars = 0;
            }

            $chunk[$key] = $unit;
            $chars += $length;
        }

        if ($chunk !== []) {
            $chunks[] = $chunk;
        }

        return $chunks;
    }

    /**
     * Translate a standalone string with Gemini -> DeepSeek -> original source text fallback.
     */
    public function translateString(string $text, string $targetLanguage, string $sourceLanguage = 'en'): string
    {
        $text = trim($text);
        if ($text === '' || strtolower($targetLanguage) === strtolower($sourceLanguage)) {
            return $text;
        }

        $unit = new TranslationUnit(
            entityType: 'general',
            entityId: 0,
            field: 'text',
            sourceLanguage: strtolower($sourceLanguage),
            targetLanguage: strtolower($targetLanguage),
            sourceText: $text,
            structuredType: 'text'
        );

        $result = $this->translateUnit($unit);

        if ($result->isSuccess && !empty(trim((string) $result->translatedText))) {
            return trim($result->translatedText);
        }

        return $text;
    }

    /**
     * Automatically detect source language based on available non-empty content in package.
     */
    public function detectSourceLanguage(Package $package): string
    {
        $titleTrans = $package->getTranslations('title');

        if (!empty($titleTrans['en'])) {
            return 'en';
        }
        if (!empty($titleTrans['ar'])) {
            return 'ar';
        }

        foreach ($titleTrans as $lang => $text) {
            if (!empty(trim((string) $text))) {
                return $lang;
            }
        }

        return config('app.fallback_locale', 'en');
    }

    /**
     * Translate missing or stale package content.
     */
    public function translatePackage(Package $package, ?string $sourceLang = null, bool $missingOnly = true): array
    {
        $sourceLang = $sourceLang ?: $this->detectSourceLanguage($package);
        $activeLangs = $this->getActiveLanguages();
        $targetLangs = array_values(array_filter($activeLangs, fn($l) => strtolower($l) !== strtolower($sourceLang)));

        $units = $this->packageSchema->extractUnits($package, $sourceLang, $targetLangs, $missingOnly);

        $resultsSummary = [
            'total_units' => count($units),
            'success_count' => 0,
            'failed_count' => 0,
            'fallback_count' => 0,
            'cached_count' => 0,
            'details' => [],
        ];

        foreach ($units as $unit) {
            $result = $this->translateUnit($unit);

            if ($result->isSuccess) {
                $resultsSummary['success_count']++;
                if ($result->provider === 'cache') {
                    $resultsSummary['cached_count']++;
                } elseif ($result->provider === 'deepseek') {
                    $resultsSummary['fallback_count']++;
                }

                $this->applyTranslationToModel($package, $unit, $result->translatedText);
            } else {
                $resultsSummary['failed_count']++;
                Log::warning("Failed to translate unit {$unit->entityType}#{$unit->entityId}:{$unit->field} to {$unit->targetLanguage}: {$result->errorMessage}");
            }

            $resultsSummary['details'][] = [
                'entity' => $unit->entityType,
                'id' => $unit->entityId,
                'field' => $unit->field,
                'target_language' => $unit->targetLanguage,
                'provider' => $result->provider,
                'success' => $result->isSuccess,
            ];
        }

        return $resultsSummary;
    }

    /**
     * Apply translated text back to Eloquent models.
     */
    protected function applyTranslationToModel(Package $package, TranslationUnit $unit, string $translatedText): void
    {
        if ($unit->entityType === 'package') {
            if ($unit->structuredType === 'json_array') {
                $decodedTranslated = json_decode($translatedText, true);
                if (is_array($decodedTranslated)) {
                    $existing = $package->{$unit->field} ?: [];
                    foreach ($decodedTranslated as $idx => $val) {
                        if (!isset($existing[$idx]) || !is_array($existing[$idx])) {
                            $existing[$idx] = [];
                        }
                        $existing[$idx][$unit->targetLanguage] = $val;
                    }
                    $package->{$unit->field} = $existing;
                    $package->save();
                }
            } else {
                $package->setTranslation($unit->field, $unit->targetLanguage, $translatedText);
                $package->save();
            }
        } elseif ($unit->entityType === 'package_faq_batch') {
            $decodedBatch = json_decode($translatedText, true);
            if (is_array($decodedBatch)) {
                $existingFaqs = $package->faq_json ?: [];
                $batchIndex = (int) str_replace('faq_json_batch_', '', $unit->field);
                $startIndex = $batchIndex * 5;

                foreach ($decodedBatch as $offset => $faqItem) {
                    $globalIndex = $startIndex + $offset;
                    if (isset($existingFaqs[$globalIndex])) {
                        $q = $existingFaqs[$globalIndex]['question'] ?? $existingFaqs[$globalIndex]['q'] ?? [];
                        $a = $existingFaqs[$globalIndex]['answer'] ?? $existingFaqs[$globalIndex]['a'] ?? [];

                        if (!is_array($q)) {
                            $q = [$unit->sourceLanguage => (string) $q];
                        }
                        if (!is_array($a)) {
                            $a = [$unit->sourceLanguage => (string) $a];
                        }

                        $q[$unit->targetLanguage] = $faqItem['q'] ?? '';
                        $a[$unit->targetLanguage] = $faqItem['a'] ?? '';

                        $existingFaqs[$globalIndex]['question'] = $q;
                        $existingFaqs[$globalIndex]['answer'] = $a;
                    }
                }

                $package->faq_json = $existingFaqs;
                $package->save();
            }
        } elseif ($unit->entityType === 'package_highlight') {
            $highlight = PackageHighlight::find($unit->entityId);
            if ($highlight) {
                $highlight->setTranslation($unit->field, $unit->targetLanguage, $translatedText);
                $highlight->save();
            }
        } elseif ($unit->entityType === 'package_inclusion') {
            $inclusion = PackageInclusion::find($unit->entityId);
            if ($inclusion) {
                $inclusion->setTranslation($unit->field, $unit->targetLanguage, $translatedText);
                $inclusion->save();
            }
        } elseif ($unit->entityType === 'itinerary') {
            $itinerary = Itinerary::find($unit->entityId);
            if ($itinerary) {
                $itinerary->setTranslation($unit->field, $unit->targetLanguage, $translatedText);
                $itinerary->save();
            }
        } elseif ($unit->entityType === 'nile_cruise_cabin') {
            $cabin = NileCruiseCabin::find($unit->entityId);
            if ($cabin) {
                $desc = $cabin->description ?: [];
                if (!is_array($desc)) {
                    $desc = [$unit->sourceLanguage => (string) $desc];
                }
                $desc[$unit->targetLanguage] = $translatedText;
                $cabin->description = $desc;
                $cabin->save();
            }
        } elseif ($unit->entityType === 'nile_cruise_itinerary_day') {
            $ncDay = NileCruiseItineraryDay::find($unit->entityId);
            if ($ncDay) {
                $ncDay->setTranslation($unit->field, $unit->targetLanguage, $translatedText);
                $ncDay->save();
            }
        }
    }
}
