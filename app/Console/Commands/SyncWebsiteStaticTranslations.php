<?php

namespace App\Console\Commands;

use App\Services\Translation\TranslationKeyValidator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncWebsiteStaticTranslations extends Command
{
    protected $signature = 'website:sync-static-translations {--path=resources/views/website}';

    protected $description = 'Move static website texts into lang JSON files and wrap safe Blade literals with __().';

    protected array $protectedBlocks = [];

    public function handle(): int
    {
        $targetPath = base_path($this->option('path'));

        if (!File::isDirectory($targetPath)) {
            $this->error("Directory not found: {$targetPath}");
            return self::FAILURE;
        }

        $files = collect(File::allFiles($targetPath))
            ->filter(fn ($file) => $file->getExtension() === 'php')
            ->values();

        $updatedFiles = 0;
        $totalReplacements = 0;

        foreach ($files as $file) {
            $path = $file->getPathname();
            $original = File::get($path);
            $replacements = 0;
            $updated = $this->localizeBladeContent($original, $path, $replacements);

            if ($updated !== $original) {
                File::put($path, $updated);
                $updatedFiles++;
                $totalReplacements += $replacements;
                $this->line("Updated: {$path} ({$replacements} replacements)");
            }
        }

        $keys = $this->collectTranslationKeys($targetPath);
        $this->syncJsonLanguageFiles($keys);

        $this->info("Website localization sync completed. Updated {$updatedFiles} files with {$totalReplacements} replacements.");
        $this->info('Language JSON files were synchronized in lang/.');

        return self::SUCCESS;
    }

    protected function localizeBladeContent(string $content, string $path, int &$replacements = 0): string
    {
        $this->protectedBlocks = [];

        $content = $this->protectBlocks($content);

        $content = preg_replace_callback(
            "/@section\\(\\s*(['\"])title\\1\\s*,\\s*(['\"])(.*?)\\2\\s*\\)/s",
            function (array $matches) use (&$replacements) {
                $text = $this->normalizeWhitespace($matches[3]);

                if (! $this->shouldTranslate($text)) {
                    return $matches[0];
                }

                $replacements++;

                return "@section('title', __('" . $this->escapeForSingleQuotedBlade($text) . "'))";
            },
            $content
        );

        $attributes = ['placeholder', 'title', 'aria-label', 'alt'];
        $attributePattern = '/\b(' . implode('|', $attributes) . ')\s*=\s*("|\')(.*?)\2/s';

        $content = preg_replace_callback(
            $attributePattern,
            function (array $matches) use (&$replacements) {
                [$full, $attribute, $quote, $value] = $matches;
                $normalized = $this->normalizeWhitespace($value);

                if (! $this->shouldTranslate($normalized) || $this->looksLikeNonTranslatableAttribute($normalized)) {
                    return $full;
                }

                $replacements++;

                return $attribute . '="{{ __(\'' . $this->escapeForSingleQuotedBlade($normalized) . '\') }}"';
            },
            $content
        );

        $content = preg_replace_callback(
            '/(<[^>]+>)([^<>]+)(<)/s',
            function (array $matches) use (&$replacements) {
                $segment = $matches[2];
                $trimmed = trim($segment);

                if ($trimmed === '') {
                    return $matches[0];
                }

                if (str_contains($segment, '{{') || str_contains($segment, '{!!') || str_contains($segment, '@')) {
                    return $matches[0];
                }

                $normalized = $this->normalizeWhitespace($trimmed);

                if (! $this->shouldTranslate($normalized)) {
                    return $matches[0];
                }

                preg_match('/^\s*/s', $segment, $leadingMatch);
                preg_match('/\s*$/s', $segment, $trailingMatch);

                $leading = $leadingMatch[0] ?? '';
                $trailing = $trailingMatch[0] ?? '';

                $replacements++;

                return $matches[1] . $leading . "{{ __('" . $this->escapeForSingleQuotedBlade($normalized) . "') }}" . $trailing . $matches[3];
            },
            $content
        );

        return $this->restoreBlocks($content);
    }

    protected function protectBlocks(string $content): string
    {
        $patterns = [
            '/<script\b[^>]*>.*?<\/script>/is',
            '/<style\b[^>]*>.*?<\/style>/is',
            '/<!--.*?-->/s',
            '/\{\-\-.*?\-\-\}/s',
        ];

        foreach ($patterns as $pattern) {
            $content = preg_replace_callback($pattern, function (array $matches) {
                $token = '__PROTECTED_BLOCK_' . count($this->protectedBlocks) . '__';
                $this->protectedBlocks[$token] = $matches[0];
                return $token;
            }, $content);
        }

        return $content;
    }

    protected function restoreBlocks(string $content): string
    {
        return strtr($content, $this->protectedBlocks);
    }

    protected function shouldTranslate(string $text): bool
    {
        if ($text === '') {
            return false;
        }

        if (preg_match('/^(#|\/|tel:|mailto:|https?:|javascript:|viber:)/i', $text)) {
            return false;
        }

        if (preg_match('/^(true|false|null)$/i', $text)) {
            return false;
        }

        if (preg_match('/^[0-9\s\W]+$/u', $text)) {
            return false;
        }

        if (str_contains($text, '{{') || str_contains($text, '}}') || str_contains($text, '@')) {
            return false;
        }

        if (str_starts_with($text, '<!--') || str_contains($text, '__PROTECTED_BLOCK_')) {
            return false;
        }

        if (str_contains($text, 'cdn-cgi') || str_contains($text, 'window.') || str_contains($text, 'document.')) {
            return false;
        }

        return (new TranslationKeyValidator())->isValid($text);
    }

    protected function looksLikeNonTranslatableAttribute(string $value): bool
    {
        return preg_match('/^(?:_blank|_self|[a-z0-9_\-\.]+@[a-z0-9\.\-]+\.[a-z]{2,})$/i', $value) === 1
            && ! preg_match('/\s/', $value)
            && ! preg_match('/[\p{Arabic}]/u', $value);
    }

    protected function normalizeWhitespace(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', $value);
        return trim($value ?? '');
    }

    protected function escapeForSingleQuotedBlade(string $value): string
    {
        return str_replace(['\\', '\''], ['\\\\', '\\\''], $value);
    }

    protected function collectTranslationKeys(string $targetPath): array
    {
        $keys = [];

        foreach (File::allFiles($targetPath) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = File::get($file->getPathname());

            preg_match_all(
                "/__\\(\\s*'((?:\\\\.|[^'\\\\])*)'\\s*(?:,\\s*\\[(?:[^\\]']|'(?:\\\\.|[^'\\\\])*')*\\])?\\s*\\)/s",
                $content,
                $singleQuoted
            );
            preg_match_all(
                '/__\\(\\s*"((?:\\\\.|[^"\\\\])*)"\\s*(?:,\\s*\\[(?:[^\\]"]|"(?:\\\\.|[^"\\\\])*")*\\])?\\s*\\)/s',
                $content,
                $doubleQuoted
            );

            foreach (array_merge($singleQuoted[1] ?? [], $doubleQuoted[1] ?? []) as $match) {
                $key = stripcslashes($match);
                $key = $this->normalizeWhitespace($key);

                if ($this->shouldTranslate($key)) {
                    $keys[$key] = $key;
                }
            }
        }

        ksort($keys, SORT_NATURAL | SORT_FLAG_CASE);

        return array_keys($keys);
    }

    protected function syncJsonLanguageFiles(array $keys): void
    {
        $langPath = lang_path();

        if (! File::isDirectory($langPath)) {
            File::ensureDirectoryExists($langPath);
        }

        $jsonFiles = collect(File::files($langPath))
            ->filter(fn ($file) => $file->getExtension() === 'json')
            ->map(fn ($file) => $file->getPathname())
            ->values()
            ->all();

        $englishPath = $langPath . DIRECTORY_SEPARATOR . 'en.json';

        if (! in_array($englishPath, $jsonFiles, true)) {
            $jsonFiles[] = $englishPath;
        }

        foreach ($jsonFiles as $jsonFile) {
            $existing = [];

            if (File::exists($jsonFile)) {
                $decoded = json_decode(File::get($jsonFile), true);
                $existing = is_array($decoded) ? $decoded : [];
            }

            $merged = [];

            foreach ($keys as $key) {
                $merged[$key] = $existing[$key] ?? $key;
            }

            foreach ($existing as $key => $value) {
                if (! array_key_exists($key, $merged)) {
                    $merged[$key] = $value;
                }
            }

            ksort($merged, SORT_NATURAL | SORT_FLAG_CASE);

            File::put(
                $jsonFile,
                json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL
            );
        }
    }
}
