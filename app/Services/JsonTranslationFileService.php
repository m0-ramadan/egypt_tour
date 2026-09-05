<?php

namespace App\Services;

use App\Services\Translation\TranslationKeyValidator;
use App\Services\Translation\TranslationValidator;
use App\Support\LocaleNormalizer;
use App\Support\RateLimitedLogger;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class JsonTranslationFileService
{
    public function __construct(
        protected DeepSeekService $deepSeekService,
        protected LocaleNormalizer $localeNormalizer,
        protected TranslationValidator $translationValidator,
        protected TranslationKeyValidator $keyValidator,
        protected RateLimitedLogger $logger
    ) {}

    /** This method is for explicit admin/console actions, never request middleware. */
    public function ensureLocaleFile(string $locale, bool $force = false): bool
    {
        $locale = $this->localeNormalizer->normalize($locale);
        $directory = $this->languageDirectory();

        if (!$this->prepareWritableDirectory($directory)) {
            return false;
        }

        $baseTranslations = $this->baseTranslations();
        if ($baseTranslations === []) {
            return true;
        }

        $path = $this->localePath($locale);
        $existing = $this->readJsonFile($path);
        $translations = $existing;
        $missing = [];

        foreach ($baseTranslations as $key => $value) {
            $currentValue = $existing[$key] ?? null;
            if (!$force && is_string($currentValue) && trim($currentValue) !== '') {
                continue;
            }

            if ($locale === $this->localeNormalizer->normalize((string) config('translation.source_locale', 'en'))) {
                $translations[$key] = $value;
            } else {
                $missing[$key] = $value;
            }
        }

        if ($missing !== []) {
            $translated = $this->translateMap($missing, $locale);
            foreach ($missing as $key => $fallback) {
                $translations[$key] = $translated[$key] ?? $fallback;
            }
        }

        foreach ($existing as $key => $value) {
            $translations[$key] ??= $value;
        }

        ksort($translations, SORT_NATURAL | SORT_FLAG_CASE);

        return $this->writeJsonFile($path, $translations);
    }

    public function renameLocaleFile(string $oldLocale, string $newLocale): bool
    {
        $oldPath = $this->localePath($this->localeNormalizer->normalize($oldLocale));
        $newPath = $this->localePath($this->localeNormalizer->normalize($newLocale));

        if ($oldPath === $newPath || !File::exists($oldPath) || File::exists($newPath)) {
            return true;
        }

        if (!$this->prepareWritableDirectory(dirname($newPath)) || !is_writable($oldPath)) {
            $this->logUnwritable($oldPath);
            return false;
        }

        try {
            return File::move($oldPath, $newPath);
        } catch (\Throwable $e) {
            $this->logger->warning('translation-file-rename', 'Translation file could not be renamed.', ['exception' => $e::class]);
            return false;
        }
    }

    public function removeLocaleFile(string $locale): bool
    {
        $path = $this->localePath($this->localeNormalizer->normalize($locale));
        if (!File::exists($path)) {
            return true;
        }
        if (!is_writable($path)) {
            $this->logUnwritable($path);
            return false;
        }

        try {
            return File::delete($path);
        } catch (\Throwable $e) {
            $this->logger->warning('translation-file-delete', 'Translation file could not be removed.', ['exception' => $e::class]);
            return false;
        }
    }

    protected function baseTranslations(): array
    {
        $sourceLocale = $this->localeNormalizer->normalize((string) config('translation.source_locale', 'en'));
        $source = $this->readJsonFile($this->localePath($sourceLocale));

        if ($source !== []) {
            foreach ($source as $key => $value) {
                $source[$key] = is_string($value) && trim($value) !== '' ? trim($value) : $key;
            }
            return $source;
        }

        $translations = [];
        if (!File::isDirectory($this->languageDirectory())) {
            return [];
        }

        foreach (File::files($this->languageDirectory()) as $file) {
            if ($file->getExtension() !== 'json') {
                continue;
            }
            foreach ($this->readJsonFile($file->getPathname()) as $key => $value) {
                $translations[$key] ??= is_string($value) && trim($value) !== '' ? trim($value) : $key;
            }
        }

        ksort($translations, SORT_NATURAL | SORT_FLAG_CASE);
        return $translations;
    }

    protected function translateMap(array $items, string $locale): array
    {
        if (!(bool) config('translation.ai_enabled', true)) {
            return $items;
        }

        $translated = [];
        $pending = [];
        $sourceLocale = $this->localeNormalizer->normalize((string) config('translation.source_locale', 'en'));

        foreach ($items as $key => $value) {
            if (!$this->keyValidator->isValid($key)) {
                $translated[$key] = $value;
                continue;
            }

            $cacheKey = $this->translationCacheKey($sourceLocale, $locale, $value);
            try {
                $cached = Cache::get($cacheKey);
            } catch (\Throwable) {
                $cached = null;
            }

            if (is_string($cached) && trim($cached) !== '') {
                $translated[$key] = $cached;
            } else {
                $pending[$key] = $value;
            }
        }

        foreach ($this->chunks($pending) as $chunk) {
            $translated += $this->translateChunk($chunk, $locale, $sourceLocale);
        }

        return $translated;
    }

    protected function translateChunk(array $items, string $locale, string $sourceLocale): array
    {
        if ($items === []) {
            return [];
        }

        $json = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json)) {
            return $items;
        }

        $prompt = "Translate all JSON values from {$sourceLocale} to {$locale}.\n"
            . "Keep keys exactly unchanged. Preserve placeholders (:count, :name, {name}) and HTML tags. "
            . "Do not add PHP, Blade, Markdown, or extra keys. Return only valid JSON as "
            . '{"translations":{"Original key":"Translated value"}}' . "\nInput JSON:\n{$json}";

        $response = $this->deepSeekService->askJson(
            prompt: $prompt,
            systemPrompt: 'You are a UI localization service. Return valid JSON only.',
            temperature: 0.1,
            maxTokens: 4000
        );

        $values = $response['translations'] ?? null;
        if (!is_array($values) || array_keys($values) !== array_keys($items)) {
            $this->logger->warning(
                'translation-json-invalid-keys:' . $locale,
                'AI translation response was rejected because its JSON keys did not match the request.',
                ['locale' => $locale]
            );
            return $items;
        }

        $result = [];
        foreach ($items as $key => $fallback) {
            $value = $values[$key] ?? null;
            if (!is_string($value) || !$this->translationValidator->validate($fallback, $value, $sourceLocale, $locale)) {
                $result[$key] = $fallback;
                continue;
            }

            $result[$key] = trim($value);
            try {
                Cache::put(
                    $this->translationCacheKey($sourceLocale, $locale, $fallback),
                    $result[$key],
                    (int) config('translation.cache_ttl', 2592000)
                );
            } catch (\Throwable) {
                // The generated file remains the durable result.
            }
        }

        return $result;
    }

    protected function readJsonFile(string $path): array
    {
        if (!File::exists($path) || !File::isReadable($path)) {
            return [];
        }

        try {
            $decoded = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable $e) {
            $this->logger->warning('translation-json-read:' . $path, 'Translation JSON file could not be read.', [
                'file' => basename($path),
                'exception' => $e::class,
            ]);
            return [];
        }
    }

    protected function writeJsonFile(string $path, array $translations): bool
    {
        if ((File::exists($path) && !is_writable($path)) || !is_writable(dirname($path))) {
            $this->logUnwritable($path);
            return false;
        }

        try {
            $json = json_encode(
                $translations,
                JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ) . PHP_EOL;
            $temporary = tempnam(dirname($path), '.translation-');
            if ($temporary === false || file_put_contents($temporary, $json, LOCK_EX) === false) {
                throw new \RuntimeException('Unable to create temporary translation file.');
            }
            if (!rename($temporary, $path)) {
                @unlink($temporary);
                throw new \RuntimeException('Unable to atomically replace translation file.');
            }
            return true;
        } catch (\Throwable $e) {
            $this->logger->warning(
                'translation-json-write:' . $path,
                'Translation JSON file could not be written; source text fallback remains active.',
                ['file' => basename($path), 'exception' => $e::class]
            );
            return false;
        }
    }

    protected function languageDirectory(): string
    {
        return base_path(trim((string) config('translation.lang_path', 'lang'), '/'));
    }

    protected function localePath(string $locale): string
    {
        return $this->languageDirectory() . DIRECTORY_SEPARATOR . $locale . '.json';
    }

    private function prepareWritableDirectory(string $directory): bool
    {
        try {
            if (!File::isDirectory($directory)) {
                $parent = dirname($directory);
                if (!is_writable($parent)) {
                    $this->logUnwritable($directory);
                    return false;
                }
                File::makeDirectory($directory, 0755, true);
            }
        } catch (\Throwable $e) {
            $this->logger->warning('translation-directory-create', 'Translation directory could not be created.', ['exception' => $e::class]);
            return false;
        }

        if (!is_writable($directory)) {
            $this->logUnwritable($directory);
            return false;
        }

        return true;
    }

    private function logUnwritable(string $path): void
    {
        $this->logger->warning(
            'translation-filesystem-unwritable:' . $path,
            'Translation path is not writable; source text fallback remains active.',
            ['path' => $path]
        );
    }

    private function translationCacheKey(string $source, string $target, string $text): string
    {
        return "translation:{$source}:{$target}:" . hash('sha256', trim($text));
    }

    private function chunks(array $items): array
    {
        $maxItems = (int) config('translation.batch_size', 30);
        $maxChars = (int) config('translation.max_chars_per_request', 8000);
        $chunks = [];
        $chunk = [];
        $characters = 0;

        foreach ($items as $key => $value) {
            $length = mb_strlen((string) $value);
            if ($chunk !== [] && (count($chunk) >= $maxItems || $characters + $length > $maxChars)) {
                $chunks[] = $chunk;
                $chunk = [];
                $characters = 0;
            }
            $chunk[$key] = $value;
            $characters += $length;
        }

        if ($chunk !== []) {
            $chunks[] = $chunk;
        }

        return $chunks;
    }
}
