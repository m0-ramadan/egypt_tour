<?php

namespace Tests\Feature;

use App\Services\JsonTranslationFileService;
use Tests\TestCase;

class TranslationRuntimeSafetyTest extends TestCase
{
    public function test_missing_translation_falls_back_without_an_ai_request(): void
    {
        config(['translation.runtime_auto_translate' => false]);

        $this->assertSame('Definitely missing translation', __('Definitely missing translation'));
    }

    public function test_unwritable_translation_directory_returns_false_instead_of_throwing(): void
    {
        $relative = 'tests/tmp-lang-' . bin2hex(random_bytes(4));
        $directory = base_path($relative);
        mkdir($directory, 0555, true);
        config(['translation.lang_path' => $relative]);

        try {
            $this->assertFalse(app(JsonTranslationFileService::class)->ensureLocaleFile('ar'));
        } finally {
            chmod($directory, 0755);
            rmdir($directory);
        }
    }

    public function test_file_cache_configuration_never_enables_geoip_tags(): void
    {
        $this->assertContains(config('cache.default'), ['file', 'array', 'database']);
        $this->assertNull(config('geoip.cache_tags'));
    }
}
