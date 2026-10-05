<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Itinerary;
use App\Models\Language;
use App\Models\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeekTranslationSeeder extends Seeder
{
    /**
     * DeepSeek API credentials and configuration.
     */
    protected string $apiKey = '';
    protected string $model = 'deepseek-chat';
    protected string $baseUrl = 'https://api.deepseek.com/v1/chat/completions';
    protected int $timeout = 60;
    protected int $maxRetries = 3;

    /**
     * Active dashboard language codes (e.g. ['en', 'fr', 'de']).
     *
     * @var array<string>
     */
    protected array $activeLanguages = [];

    /**
     * Translatable attributes on Package model.
     *
     * @var array<string>
     */
    protected array $packageFields = [
        'title',
        'subtitle',
        'short_description',
        'description',
        'schedule_text',
        'pickup_location',
        'dropoff_location',
        'destinations_text',
        'location_summary',
        'cancellation_policy',
        'terms_conditions',
        'seo_title',
        'seo_description',
        'breadcrumb_title',
    ];

    /**
     * Translatable attributes on Itinerary model.
     *
     * @var array<string>
     */
    protected array $itineraryFields = [
        'title',
        'description',
        'overnight_location',
        'accommodation',
        'transport_notes',
    ];

    /**
     * Translatable attributes on Article model.
     *
     * @var array<string>
     */
    protected array $articleFields = [
        'title',
        'excerpt',
        'content',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    /**
     * Execution stats.
     */
    protected array $stats = [
        'packages_checked' => 0,
        'packages_updated' => 0,
        'itineraries_updated' => 0,
        'articles_checked' => 0,
        'articles_updated' => 0,
        'api_calls' => 0,
        'errors' => 0,
    ];

    /**
     * Dry run mode (read-only, does not save changes).
     */
    protected bool $dryRun = false;

    public function __construct(bool $dryRun = false)
    {
        $this->dryRun = $dryRun;
        $this->apiKey = (string) (config('services.deepseek.api_key') ?: env('DEEPSEEK_API_KEY', ''));
        $this->model = (string) (config('services.deepseek.model') ?: env('DEEPSEEK_MODEL', 'deepseek-chat'));
        $this->baseUrl = (string) (config('services.deepseek.base_url') ?: env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com/v1/chat/completions'));
        $this->timeout = (int) (config('services.deepseek.timeout') ?: 60);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->logInfo('=== Starting DeepSeek Translation Seeder ===');

        if (empty($this->apiKey)) {
            $this->logError('DeepSeek API Key is missing! Please configure DEEPSEEK_API_KEY in .env');
            return;
        }

        // 1. Fetch active languages from dashboard
        $this->activeLanguages = $this->resolveActiveLanguages();
        $this->logInfo('Active Dashboard Languages: ' . implode(', ', $this->activeLanguages));

        if (count($this->activeLanguages) <= 1) {
            $this->logWarn('Only 1 active language found. Nothing to translate across languages.');
            return;
        }

        // 2. Process all Packages
        $this->processPackages();

        // 3. Process all Articles
        $this->processArticles();

        // 4. Output summary
        $this->logInfo('=== DeepSeek Translation Seeder Completed ===');
        $this->logInfo(sprintf(
            'Packages Checked: %d | Updated: %d | Itineraries Updated: %d',
            $this->stats['packages_checked'],
            $this->stats['packages_updated'],
            $this->stats['itineraries_updated']
        ));
        $this->logInfo(sprintf(
            'Articles Checked: %d | Updated: %d',
            $this->stats['articles_checked'],
            $this->stats['articles_updated']
        ));
        $this->logInfo(sprintf(
            'Total DeepSeek API Calls: %d | Errors: %d',
            $this->stats['api_calls'],
            $this->stats['errors']
        ));
    }

    /**
     * Resolve active languages dynamically from the database.
     */
    public function resolveActiveLanguages(): array
    {
        try {
            $codes = Language::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->pluck('code')
                ->map(fn($c) => strtolower(trim((string) $c)))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($codes)) {
                return $codes;
            }
        } catch (\Throwable $e) {
            $this->logWarn('Could not query languages table: ' . $e->getMessage());
        }

        return ['en', 'fr', 'de'];
    }

    /**
     * Iterate through all Packages and translate missing fields.
     */
    protected function processPackages(): void
    {
        $packages = Package::with('itineraries')->get();
        $total = $packages->count();
        $this->logInfo("Found {$total} packages to inspect.");

        foreach ($packages as $index => $package) {
            $this->stats['packages_checked']++;
            $itemNum = $index + 1;
            $packageTitle = $package->name ?: ('Package #' . $package->id);

            $dirty = false;
            $missingFieldsMap = []; // [field => ['source_text' => '...', 'missing_langs' => ['fr', 'de']]]

            foreach ($this->packageFields as $field) {
                $raw = $package->getRawOriginal($field);
                $transArray = is_array($raw) ? $raw : json_decode((string) $raw, true);

                if (!is_array($transArray)) {
                    $transArray = !empty($raw) ? ['en' => (string) $raw] : [];
                }

                $sourceText = $this->extractBestSourceText($transArray);
                if (empty($sourceText)) {
                    continue;
                }

                $missingLangs = $this->getMissingLanguages($transArray);
                if (!empty($missingLangs)) {
                    $missingFieldsMap[$field] = [
                        'source' => $sourceText,
                        'current' => $transArray,
                        'missing' => $missingLangs,
                    ];
                }
            }

            if (!empty($missingFieldsMap)) {
                $this->logLine("[{$itemNum}/{$total}] Translating package #{$package->id}: {$packageTitle} (" . count($missingFieldsMap) . " fields)...");

                $translatedData = $this->translateBatchForLanguages($missingFieldsMap);

                foreach ($missingFieldsMap as $field => $info) {
                    $updatedTranslations = $info['current'];
                    foreach ($info['missing'] as $targetLang) {
                        if (!empty($translatedData[$field][$targetLang])) {
                            $updatedTranslations[$targetLang] = $translatedData[$field][$targetLang];
                            $dirty = true;
                        }
                    }
                    $package->setAttribute($field, $updatedTranslations);
                }
            }

            // Check package itineraries
            $itinerariesUpdated = $this->processItineraries($package);

            if ($dirty) {
                if (!$this->dryRun) {
                    $package->saveQuietly();
                }
                $this->stats['packages_updated']++;
                $this->logSuccess(" -> Package #{$package->id} updated successfully.");
            } elseif ($itinerariesUpdated > 0) {
                $this->logSuccess(" -> Package #{$package->id} itineraries updated.");
            }
        }
    }

    /**
     * Inspect and translate missing itinerary translations for a package.
     */
    protected function processItineraries(Package $package): int
    {
        $updatedCount = 0;

        foreach ($package->itineraries as $itinerary) {
            $dirty = false;
            $missingFieldsMap = [];

            foreach ($this->itineraryFields as $field) {
                $raw = $itinerary->getRawOriginal($field);
                $transArray = is_array($raw) ? $raw : json_decode((string) $raw, true);

                if (!is_array($transArray)) {
                    $transArray = !empty($raw) ? ['en' => (string) $raw] : [];
                }

                $sourceText = $this->extractBestSourceText($transArray);
                if (empty($sourceText)) {
                    continue;
                }

                $missingLangs = $this->getMissingLanguages($transArray);
                if (!empty($missingLangs)) {
                    $missingFieldsMap[$field] = [
                        'source' => $sourceText,
                        'current' => $transArray,
                        'missing' => $missingLangs,
                    ];
                }
            }

            if (!empty($missingFieldsMap)) {
                $translatedData = $this->translateBatchForLanguages($missingFieldsMap);

                foreach ($missingFieldsMap as $field => $info) {
                    $updatedTranslations = $info['current'];
                    foreach ($info['missing'] as $targetLang) {
                        if (!empty($translatedData[$field][$targetLang])) {
                            $updatedTranslations[$targetLang] = $translatedData[$field][$targetLang];
                            $dirty = true;
                        }
                    }
                    $itinerary->setAttribute($field, $updatedTranslations);
                }

                if ($dirty) {
                    if (!$this->dryRun) {
                        $itinerary->saveQuietly();
                    }
                    $this->stats['itineraries_updated']++;
                    $updatedCount++;
                }
            }
        }

        return $updatedCount;
    }

    /**
     * Iterate through all Articles and translate missing fields.
     */
    protected function processArticles(): void
    {
        $articles = Article::all();
        $total = $articles->count();
        $this->logInfo("Found {$total} articles to inspect.");

        foreach ($articles as $index => $article) {
            $this->stats['articles_checked']++;
            $itemNum = $index + 1;
            $articleTitle = $article->display_title ?: ('Article #' . $article->id);

            $dirty = false;
            $missingFieldsMap = [];

            foreach ($this->articleFields as $field) {
                $raw = $article->getRawOriginal($field);
                $transArray = is_array($raw) ? $raw : json_decode((string) $raw, true);

                if (!is_array($transArray)) {
                    $transArray = !empty($raw) ? ['en' => (string) $raw] : [];
                }

                $sourceText = $this->extractBestSourceText($transArray);
                if (empty($sourceText)) {
                    continue;
                }

                $missingLangs = $this->getMissingLanguages($transArray);
                if (!empty($missingLangs)) {
                    $missingFieldsMap[$field] = [
                        'source' => $sourceText,
                        'current' => $transArray,
                        'missing' => $missingLangs,
                    ];
                }
            }

            if (!empty($missingFieldsMap)) {
                $this->logLine("[{$itemNum}/{$total}] Translating article #{$article->id}: {$articleTitle} (" . count($missingFieldsMap) . " fields)...");

                // If content is very large, translate it separately to protect token limits
                $largeContent = null;
                if (isset($missingFieldsMap['content']) && mb_strlen($missingFieldsMap['content']['source']) > 2000) {
                    $largeContent = $missingFieldsMap['content'];
                    unset($missingFieldsMap['content']);
                }

                $translatedData = [];
                if (!empty($missingFieldsMap)) {
                    $translatedData = $this->translateBatchForLanguages($missingFieldsMap);
                }

                // Handle large content separately if present
                if ($largeContent !== null) {
                    $contentTranslations = $this->translateLargeHtmlText(
                        $largeContent['source'],
                        $largeContent['missing']
                    );
                    $translatedData['content'] = $contentTranslations;
                    $missingFieldsMap['content'] = $largeContent;
                }

                foreach ($missingFieldsMap as $field => $info) {
                    $updatedTranslations = $info['current'];
                    foreach ($info['missing'] as $targetLang) {
                        if (!empty($translatedData[$field][$targetLang])) {
                            $updatedTranslations[$targetLang] = $translatedData[$field][$targetLang];
                            $dirty = true;
                        }
                    }
                    $article->setAttribute($field, $updatedTranslations);
                }

                if ($dirty) {
                    if (!$this->dryRun) {
                        $article->saveQuietly();
                    }
                    $this->stats['articles_updated']++;
                    $this->logSuccess(" -> Article #{$article->id} updated successfully.");
                }
            }
        }
    }

    /**
     * Batch translation of multiple fields into target languages using DeepSeek.
     *
     * @param array<string, array{source: string, current: array, missing: array}> $fieldsMap
     * @return array<string, array<string, string>> [field => [lang => translated_text]]
     */
    protected function translateBatchForLanguages(array $fieldsMap): array
    {
        // Group by required languages
        $allMissingLangs = [];
        $payloadData = [];

        foreach ($fieldsMap as $field => $info) {
            $payloadData[$field] = $info['source'];
            foreach ($info['missing'] as $lang) {
                $allMissingLangs[$lang] = true;
            }
        }

        $targetLangs = array_keys($allMissingLangs);
        if (empty($targetLangs) || empty($payloadData)) {
            return [];
        }

        $langsListStr = implode(', ', $targetLangs);

        $systemPrompt = "You are a professional travel localization expert. "
            . "Translate the input JSON values into the requested languages: {$langsListStr}. "
            . "Preserve all HTML tags, brackets, and markdown exactly as they are. "
            . "Return ONLY a valid JSON object where top-level keys are the language codes ({$langsListStr}) "
            . "and their values are objects with the exact same keys as the input. "
            . "Do NOT return markdown fences (```json), explanations, or notes.";

        $userPrompt = "Target Languages: [{$langsListStr}]\n\n"
            . "Input JSON:\n" . json_encode($payloadData, JSON_UNESCAPED_UNICODE);

        $responseJson = $this->callDeepSeek($userPrompt, $systemPrompt, true);

        if (!$responseJson) {
            return [];
        }

        // Convert structure: response is [lang => [field => translation]]
        // We want: [field => [lang => translation]]
        $result = [];
        foreach ($targetLangs as $lang) {
            if (!empty($responseJson[$lang]) && is_array($responseJson[$lang])) {
                foreach ($responseJson[$lang] as $field => $val) {
                    if (is_string($val) && trim($val) !== '') {
                        $result[$field][$lang] = trim($val);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Translate large HTML content chunk by chunk or per language to prevent truncation.
     *
     * @param string $sourceHtml
     * @param array<string> $targetLanguages
     * @return array<string, string> [lang => translated_html]
     */
    protected function translateLargeHtmlText(string $sourceHtml, array $targetLanguages): array
    {
        $results = [];

        foreach ($targetLanguages as $lang) {
            $systemPrompt = "You are an expert travel translator. "
                . "Translate the following HTML text into {$lang}. "
                . "IMPORTANT: Preserve all HTML tags (<p>, <h2>, <h3>, <strong>, <em>, <ul>, <li>, <a>, <img>) intact and in the same positions. "
                . "Do NOT add code fences (```html) or explanation. Return only the translated HTML text.";

            $translated = $this->callDeepSeekText($sourceHtml, $systemPrompt);
            if ($translated) {
                $results[$lang] = $translated;
            }
        }

        return $results;
    }

    /**
     * Call DeepSeek and parse JSON response.
     */
    public function callDeepSeek(string $prompt, string $systemPrompt, bool $expectJson = true): ?array
    {
        $this->stats['api_calls']++;

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $payload = [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.2,
                ];

                if ($expectJson) {
                    $payload['response_format'] = ['type' => 'json_object'];
                }

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->timeout($this->timeout)
                ->post($this->baseUrl, $payload);

                if ($response->successful()) {
                    $content = $response->json('choices.0.message.content');
                    if (!$content) {
                        return null;
                    }

                    $clean = $this->cleanJsonString($content);
                    $decoded = json_decode($clean, true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        return $decoded;
                    }

                    $this->logWarn("JSON parse error on attempt {$attempt}: " . json_last_error_msg());
                } else {
                    $status = $response->status();
                    $this->logWarn("DeepSeek API returned HTTP {$status} (attempt {$attempt}/{$this->maxRetries})");

                    if ($status === 429) {
                        // Rate limit wait
                        sleep(5 * $attempt);
                        continue;
                    }
                }
            } catch (\Throwable $e) {
                $this->logWarn("DeepSeek request exception on attempt {$attempt}: " . $e->getMessage());
            }

            if ($attempt < $this->maxRetries) {
                sleep(2 * $attempt);
            }
        }

        $this->stats['errors']++;
        return null;
    }

    /**
     * Call DeepSeek for plain text (e.g. large HTML content).
     */
    public function callDeepSeekText(string $prompt, string $systemPrompt): ?string
    {
        $this->stats['api_calls']++;

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->timeout($this->timeout)
                ->post($this->baseUrl, [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.2,
                ]);

                if ($response->successful()) {
                    $content = $response->json('choices.0.message.content');
                    if ($content) {
                        $clean = trim($content);
                        $clean = preg_replace('/^```html\s*/i', '', $clean);
                        $clean = preg_replace('/^```\s*/i', '', $clean);
                        $clean = preg_replace('/\s*```$/', '', $clean);
                        return trim($clean);
                    }
                }
            } catch (\Throwable $e) {
                $this->logWarn("DeepSeek text request error: " . $e->getMessage());
            }

            sleep(2 * $attempt);
        }

        $this->stats['errors']++;
        return null;
    }

    /**
     * Extract the best existing source text from a translatable array.
     */
    protected function extractBestSourceText(array $translations): ?string
    {
        // Prioritize English, then Arabic, then any non-empty string
        $preferredKeys = ['en', 'ar', 'fr', 'de'];
        foreach ($preferredKeys as $key) {
            if (!empty($translations[$key]) && is_string($translations[$key]) && trim($translations[$key]) !== '' && trim($translations[$key]) !== 'Array') {
                return trim($translations[$key]);
            }
        }

        foreach ($translations as $val) {
            if (is_string($val) && trim($val) !== '' && trim($val) !== 'Array') {
                return trim($val);
            }
        }

        return null;
    }

    /**
     * Determine which active languages are missing from the given translations array.
     *
     * @return array<string>
     */
    protected function getMissingLanguages(array $translations): array
    {
        $missing = [];

        foreach ($this->activeLanguages as $lang) {
            if (!isset($translations[$lang])) {
                $missing[] = $lang;
                continue;
            }

            $val = $translations[$lang];
            if ($val === null || (is_string($val) && (trim($val) === '' || trim($val) === 'Array'))) {
                $missing[] = $lang;
            }
        }

        return $missing;
    }

    /**
     * Clean markdown and code wrappers from a JSON response string.
     */
    protected function cleanJsonString(string $raw): string
    {
        $clean = trim($raw);
        $clean = preg_replace('/^```json\s*/i', '', $clean);
        $clean = preg_replace('/^```\s*/i', '', $clean);
        $clean = preg_replace('/\s*```$/', '', $clean);
        return trim($clean);
    }

    protected function logInfo(string $message): void
    {
        if (isset($this->command)) {
            $this->command->info($message);
        } else {
            echo "\033[32m[INFO] " . $message . "\033[0m\n";
        }
        Log::info('[DeepSeekTranslationSeeder] ' . $message);
    }

    protected function logWarn(string $message): void
    {
        if (isset($this->command)) {
            $this->command->warn($message);
        } else {
            echo "\033[33m[WARN] " . $message . "\033[0m\n";
        }
        Log::warning('[DeepSeekTranslationSeeder] ' . $message);
    }

    protected function logError(string $message): void
    {
        if (isset($this->command)) {
            $this->command->error($message);
        } else {
            echo "\033[31m[ERROR] " . $message . "\033[0m\n";
        }
        Log::error('[DeepSeekTranslationSeeder] ' . $message);
    }

    protected function logSuccess(string $message): void
    {
        if (isset($this->command)) {
            $this->command->line("<fg=green>{$message}</>");
        } else {
            echo "\033[32m" . $message . "\033[0m\n";
        }
    }

    protected function logLine(string $message): void
    {
        if (isset($this->command)) {
            $this->command->line($message);
        } else {
            echo $message . "\n";
        }
    }
}
