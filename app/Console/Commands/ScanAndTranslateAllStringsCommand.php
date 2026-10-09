<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;

class ScanAndTranslateAllStringsCommand extends Command
{
    protected $signature = 'translations:scan-and-translate
                            {--force : Re-translate every string in the selected locale(s)}
                            {--mixed-only : Only repair German values that are identical to non-English French values}
                            {--broken-placeholders : Only repair translations with changed or missing placeholders}
                            {--locale=* : Only update these target locales (fr and/or de)}';

    protected $description = 'Scan all blade templates and controllers for translation keys and translate missing strings into French and German';

    protected array $targetLocales = ['fr', 'de'];

    public function handle(): int
    {
        $this->info("Scanning codebase for translation keys...");

        $extractedKeys = $this->scanCodebase();
        $this->info("Found " . count($extractedKeys) . " unique translation keys across blade templates and controllers.");

        $enPath = base_path('lang/en.json');
        $frPath = base_path('lang/fr.json');
        $dePath = base_path('lang/de.json');

        $enData = File::exists($enPath) ? json_decode(File::get($enPath), true) ?: [] : [];
        $frData = File::exists($frPath) ? json_decode(File::get($frPath), true) ?: [] : [];
        $deData = File::exists($dePath) ? json_decode(File::get($dePath), true) ?: [] : [];

        // Merge extracted keys into en.json
        foreach ($extractedKeys as $key) {
            if (!isset($enData[$key])) {
                $enData[$key] = $key;
            }
        }
        ksort($enData, SORT_NATURAL | SORT_FLAG_CASE);
        File::put($enPath, json_encode($enData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->info("Updated lang/en.json (" . count($enData) . " keys)");

        $selectedLocales = array_values(array_intersect(
            $this->targetLocales,
            array_map('strtolower', (array) $this->option('locale'))
        ));
        $selectedLocales = $selectedLocales ?: $this->targetLocales;

        // Process French & German translations.
        if (in_array('fr', $selectedLocales, true)) {
            $this->translateLocaleFile('fr', $frPath, $enData, $frData);
        }
        if (in_array('de', $selectedLocales, true)) {
            $this->translateLocaleFile('de', $dePath, $enData, $deData);
        }

        $this->info("Translation scanning and file generation completed successfully!");
        return Command::SUCCESS;
    }

    protected function scanCodebase(): array
    {
        $directories = [
            resource_path('views'),
            app_path('Http/Controllers'),
            app_path('Services'),
        ];

        $keys = [];
        $patterns = [
            '/__\(\s*[\'"](.*?)[\'"]\s*[\),]/s',
            '/@lang\(\s*[\'"](.*?)[\'"]\s*[\),]/s',
            '/trans\(\s*[\'"](.*?)[\'"]\s*[\),]/s',
        ];

        foreach ($directories as $dir) {
            if (!File::isDirectory($dir)) continue;

            $files = File::allFiles($dir);
            foreach ($files as $file) {
                $content = File::get($file->getPathname());
                foreach ($patterns as $pattern) {
                    if (preg_match_all($pattern, $content, $matches)) {
                        foreach ($matches[1] as $key) {
                            $key = trim(stripslashes($key));
                            if (!empty($key) && !str_contains($key, '->')) {
                                $keys[$key] = true;
                            }
                        }
                    }
                }
            }
        }

        return array_keys($keys);
    }

    protected function translateLocaleFile(string $locale, string $filePath, array $enData, array $existingData): void
    {
        $this->info("Processing translations for locale: {$locale}...");
        $count = 0;
        $pending = [];
        $frenchData = $locale === 'de'
            ? (json_decode(File::get(base_path('lang/fr.json')), true) ?: [])
            : [];

        foreach ($enData as $key => $enValue) {
            $current = $existingData[$key] ?? null;

            // If missing or equals source English string (and not a single word code), translate it
            $isTranslatableText = strlen($key) > 1 && preg_match('/[a-zA-Z]{2,}/', $key);
            $looksMixed = $locale === 'de'
                && isset($frenchData[$key])
                && $current === $frenchData[$key]
                && $current !== ($enValue ?: $key);
            $hasBrokenPlaceholders = $this->placeholders($enValue ?: $key) !== $this->placeholders((string) $current);
            $needsTranslation = match (true) {
                (bool) $this->option('mixed-only') => $looksMixed,
                (bool) $this->option('broken-placeholders') => $hasBrokenPlaceholders,
                default => $this->option('force') || empty($current) || $current === $key || $looksMixed || $hasBrokenPlaceholders,
            };

            if ($isTranslatableText && $needsTranslation) {
                $pending[$key] = $enValue ?: $key;
            }
        }

        foreach (array_chunk($pending, 5, true) as $chunk) {
            foreach ($this->requestTranslationBatch($chunk, $locale) as $key => $translated) {
                if ($translated !== '') {
                    $existingData[$key] = $translated;
                    $count++;
                }
            }

            ksort($existingData, SORT_NATURAL | SORT_FLAG_CASE);
            File::put($filePath, json_encode($existingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->line("Translated and saved {$count} strings for {$locale}...");
        }

        ksort($existingData, SORT_NATURAL | SORT_FLAG_CASE);
        File::put($filePath, json_encode($existingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->info("Saved lang/{$locale}.json (" . count($existingData) . " keys, {$count} new translations)");
    }

    protected function requestTranslationBatch(array $texts, string $targetLang): array
    {
        $keys = array_keys($texts);
        $placeholderMaps = [];
        $maskedTexts = [];
        foreach ($texts as $key => $text) {
            $index = 0;
            $placeholderMaps[$key] = [];
            $maskedTexts[$key] = preg_replace_callback(
                '/:[A-Za-z_][A-Za-z0-9_]*|\{[A-Za-z_][A-Za-z0-9_]*\}/',
                function (array $match) use (&$index, &$placeholderMaps, $key): string {
                    $token = '<span class="notranslate" data-placeholder="' . $index . '">' . $match[0] . '</span>';
                    $placeholderMaps[$key][$token] = $match[0];
                    $index++;
                    return $token;
                },
                $text
            );
        }

        $responses = Http::pool(fn (Pool $pool): array => array_map(
            fn (string $text) => $pool->retry(2, 750)->timeout(10)->get('https://translate.googleapis.com/translate_a/single', [
                'client' => 'dict-chrome-ex',
                'sl' => 'en',
                'tl' => $targetLang,
                'dt' => 't',
                'q' => trim($text),
            ]),
            array_values($maskedTexts)
        ));

        $translated = [];
        foreach ($responses as $index => $response) {
            $text = '';
            if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                foreach (($response->json()[0] ?? []) as $segment) {
                    $text .= $segment[0] ?? '';
                }
            }
            $key = $keys[$index];
            foreach ($placeholderMaps[$key] as $token => $placeholder) {
                $text = str_replace($token, $placeholder, $text);
            }
            $translated[$key] = trim($text);
        }

        return $translated;
    }

    protected function placeholders(string $text): array
    {
        preg_match_all('/:[A-Za-z_][A-Za-z0-9_]*|\{[A-Za-z_][A-Za-z0-9_]*\}/', $text, $matches);
        $placeholders = $matches[0];
        sort($placeholders);

        return $placeholders;
    }

    protected function requestTranslation(string $text, string $targetLang): string
    {
        $text = trim($text);
        if ($text === '') return '';

        try {
            $response = Http::timeout(5)->get('https://translate.googleapis.com/translate_a/single', [
                'client' => 'dict-chrome-ex',
                'sl' => 'en',
                'tl' => $targetLang,
                'dt' => 't',
                'q' => $text,
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $translated = '';
                if (isset($json[0]) && is_array($json[0])) {
                    foreach ($json[0] as $segment) {
                        $translated .= $segment[0] ?? '';
                    }
                }
                return trim($translated) ?: $text;
            }
        } catch (\Throwable $e) {
            // Silently fallback to original text if request fails
        }

        return $text;
    }
}
