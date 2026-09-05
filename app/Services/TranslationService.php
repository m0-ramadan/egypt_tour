<?php

namespace App\Services;

use App\Models\Language;
use App\Services\Translation\AiTranslationService;
use App\Support\LocaleNormalizer;

class TranslationService
{
    public function __construct(
        protected AiTranslationService $aiTranslationService,
        protected LocaleNormalizer $localeNormalizer
    ) {}

    public function translateTextToAllLanguages(string|array|null $value): array
    {
        if (is_array($value)) {
            return $this->normalizeTranslations($value);
        }

        $value = trim((string) $value);

        if ($value === '') {
            return [];
        }

        if ($this->looksLikeJson($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->normalizeTranslations($decoded);
            }
        }

        $languages = $this->activeLanguages();

        $translated = $this->translateUsingAi($value, $languages);

        if (!$translated) {
            return $this->fallbackTranslations($value, $languages);
        }

        return $this->normalizeTranslations($translated, $value, $languages);
    }

    public function translateFields(array $data, array $fields): array
    {
        $pendingBySource = [];

        foreach ($fields as $field) {
            if (!array_key_exists($field, $data) || blank($data[$field])) {
                continue;
            }

            if (is_array($data[$field])) {
                $data[$field] = $this->normalizeTranslations($data[$field]);
                continue;
            }

            $text = trim((string) $data[$field]);
            if ($text === '') {
                continue;
            }

            $source = $this->detectSourceLanguage($text);
            $pendingBySource[$source][$field] = $text;
        }

        $languages = $this->activeLanguages();

        foreach ($pendingBySource as $source => $items) {
            $translatedFields = [];

            foreach ($languages as $language) {
                if ($language === $source) {
                    foreach ($items as $field => $text) {
                        $translatedFields[$field][$language] = $text;
                    }
                    continue;
                }

                $batch = $this->aiTranslationService->translateBatch($items, $language, $source);
                foreach ($items as $field => $text) {
                    $translatedFields[$field][$language] = $batch[$field] ?? $text;
                }
            }

            foreach ($items as $field => $text) {
                $data[$field] = $this->normalizeTranslations(
                    $translatedFields[$field] ?? [],
                    $text,
                    $languages
                );
            }
        }

        return $data;
    }

    public function translateTextToLanguages(string $text, array $languageCodes): array
    {
        $text = trim($text);

        if ($text === '') {
            return [];
        }

        $translated = $this->translateUsingAi($text, $languageCodes);

        if (!$translated) {
            return $this->fallbackTranslations($text, $languageCodes);
        }

        return $this->normalizeTranslations($translated, $text, $languageCodes);
    }

    /** @param array<string, string> $texts */
    public function translateTextsToLanguage(array $texts, string $targetLanguage): array
    {
        $targetLanguage = $this->localeNormalizer->normalize($targetLanguage);
        $result = $texts;
        $groups = [];

        foreach ($texts as $key => $text) {
            $text = trim((string) $text);
            $result[$key] = $text;
            if ($text !== '') {
                $groups[$this->detectSourceLanguage($text)][$key] = $text;
            }
        }

        foreach ($groups as $sourceLanguage => $items) {
            if ($sourceLanguage === $targetLanguage) {
                continue;
            }
            $result = array_replace(
                $result,
                $this->aiTranslationService->translateBatch($items, $targetLanguage, $sourceLanguage)
            );
        }

        return $result;
    }

    protected function translateUsingAi(string $text, array $languages): ?array
    {
        $sourceLang = $this->detectSourceLanguage($text);
        $result = [];

        foreach ($languages as $lang) {
            $langCode = $this->localeNormalizer->normalize((string) $lang);
            if ($langCode === $sourceLang) {
                $result[$langCode] = $text;
            } else {
                $result[$langCode] = $this->aiTranslationService->translateBatch(
                    ['text' => $text],
                    $langCode,
                    $sourceLang
                )['text'] ?? $text;
            }
        }

        return $result;
    }

    protected function detectSourceLanguage(string $text): string
    {
        if (preg_match('/\p{Arabic}/u', $text)) {
            return 'ar';
        }

        return 'en';
    }

    protected function normalizeTranslations(array $translations, ?string $original = null, array $languages = []): array
    {
        $clean = [];

        foreach ($translations as $lang => $value) {
            if (!is_string($lang) || trim($lang) === '') {
                continue;
            }

            $normalizedLocale = $this->localeNormalizer->normalize($lang);
            $clean[$normalizedLocale] = is_string($value) ? trim($value) : '';
        }

        foreach ($languages as $lang) {
            if (!array_key_exists($lang, $clean) || $clean[$lang] === '') {
                $clean[$lang] = $original ?? '';
            }
        }

        return $clean;
    }

    protected function fallbackTranslations(string $text, array $languages): array
    {
        $result = [];

        foreach ($languages as $language) {
            $result[$language] = $text;
        }

        return $result;
    }

    protected function looksLikeJson(string $value): bool
    {
        $value = trim($value);

        return str_starts_with($value, '{') && str_ends_with($value, '}');
    }

    private function activeLanguages(): array
    {
        try {
            $languages = Language::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->pluck('code')
                ->all();
        } catch (\Throwable) {
            $languages = (array) config('translation.supported_locales', ['en', 'ar']);
        }

        return array_values(array_unique(array_merge(
            $this->localeNormalizer->normalizeList($languages),
            ['en', 'ar']
        )));
    }
}
