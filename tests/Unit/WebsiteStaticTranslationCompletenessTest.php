<?php

namespace Tests\Unit;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class WebsiteStaticTranslationCompletenessTest extends TestCase
{
    public function test_every_website_translation_key_exists_in_all_public_locales(): void
    {
        $keys = $this->websiteTranslationKeys();

        foreach (['en', 'fr', 'de'] as $locale) {
            $translations = json_decode(
                file_get_contents(lang_path("{$locale}.json")),
                true,
                flags: JSON_THROW_ON_ERROR
            );

            $missing = array_values(array_diff($keys, array_keys($translations)));

            $this->assertSame([], $missing, "Missing {$locale} website translations:\n" . implode("\n", $missing));
        }
    }

    public function test_german_catalog_does_not_reuse_french_sentences(): void
    {
        $english = json_decode(file_get_contents(lang_path('en.json')), true, flags: JSON_THROW_ON_ERROR);
        $french = json_decode(file_get_contents(lang_path('fr.json')), true, flags: JSON_THROW_ON_ERROR);
        $german = json_decode(file_get_contents(lang_path('de.json')), true, flags: JSON_THROW_ON_ERROR);

        $mixed = [];
        $legitimateSharedTranslations = ['Address', 'Gallery'];
        foreach ($this->websiteTranslationKeys() as $key) {
            if (isset($english[$key], $french[$key], $german[$key])
                && !in_array($key, $legitimateSharedTranslations, true)
                && $french[$key] !== $english[$key]
                && $german[$key] === $french[$key]
                && preg_match('/[A-Za-zÀ-ÿ]{4}/u', $german[$key])) {
                $mixed[$key] = $german[$key];
            }
        }

        $this->assertSame([], $mixed, "French values found in the German catalog:\n" . print_r($mixed, true));
    }

    public function test_placeholders_are_identical_in_every_public_locale(): void
    {
        $english = json_decode(file_get_contents(lang_path('en.json')), true, flags: JSON_THROW_ON_ERROR);

        foreach (['fr', 'de'] as $locale) {
            $translations = json_decode(file_get_contents(lang_path("{$locale}.json")), true, flags: JSON_THROW_ON_ERROR);
            $broken = [];

            foreach ($this->websiteTranslationKeys() as $key) {
                $expected = $this->placeholders($english[$key] ?? $key);
                $actual = $this->placeholders($translations[$key] ?? '');
                if ($expected !== $actual) {
                    $broken[$key] = compact('expected', 'actual');
                }
            }

            $this->assertSame([], $broken, "Broken {$locale} placeholders:\n" . print_r($broken, true));
        }
    }

    private function placeholders(string $text): array
    {
        preg_match_all('/:[A-Za-z_][A-Za-z0-9_]*|\{[A-Za-z_][A-Za-z0-9_]*\}/', $text, $matches);
        $placeholders = $matches[0];
        sort($placeholders);

        return $placeholders;
    }

    /** @return list<string> */
    private function websiteTranslationKeys(): array
    {
        $directories = [
            resource_path('views/website'),
            app_path('Http/Controllers/Website'),
        ];
        $keys = [];

        foreach ($directories as $directory) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
            foreach ($files as $file) {
                if (!$file->isFile() || !in_array($file->getExtension(), ['php', 'blade.php'], true)) {
                    continue;
                }

                $contents = file_get_contents($file->getPathname());
                preg_match_all('/(?:__|trans)\(\s*([\'\"])((?:\\\\.|(?!\1).)*)\1/sU', $contents, $matches);
                foreach ($matches[2] as $key) {
                    $keys[stripslashes($key)] = true;
                }
            }
        }

        return array_keys($keys);
    }
}
