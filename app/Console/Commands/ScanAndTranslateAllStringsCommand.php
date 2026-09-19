<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class ScanAndTranslateAllStringsCommand extends Command
{
    protected $signature = 'translations:scan-and-translate {--force : Re-translate untranslated strings}';

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

        // Process French & German translations
        $this->translateLocaleFile('fr', $frPath, $enData, $frData);
        $this->translateLocaleFile('de', $dePath, $enData, $deData);

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
                            if (!empty($key) && !str_contains($key, '$') && !str_contains($key, '->')) {
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

        foreach ($enData as $key => $enValue) {
            $current = $existingData[$key] ?? null;

            // If missing or equals source English string (and not a single word code), translate it
            if (empty($current) || ($current === $key && strlen($key) > 4 && preg_match('/[a-zA-Z]{3,}/', $key))) {
                $translated = $this->requestTranslation($enValue ?: $key, $locale);
                if (!empty($translated)) {
                    $existingData[$key] = $translated;
                    $count++;

                    if ($count % 25 === 0) {
                        ksort($existingData, SORT_NATURAL | SORT_FLAG_CASE);
                        File::put($filePath, json_encode($existingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                        $this->line("Translated and saved {$count} strings for {$locale}...");
                    }
                }
            }
        }

        ksort($existingData, SORT_NATURAL | SORT_FLAG_CASE);
        File::put($filePath, json_encode($existingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->info("Saved lang/{$locale}.json (" . count($existingData) . " keys, {$count} new translations)");
    }

    protected function requestTranslation(string $text, string $targetLang): string
    {
        $text = trim($text);
        if ($text === '') return '';

        try {
            $response = Http::timeout(5)->get('https://translate.googleapis.com/translate_a/single', [
                'client' => 'gtx',
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
