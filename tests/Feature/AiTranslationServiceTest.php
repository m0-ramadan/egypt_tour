<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Itinerary;
use App\Services\Translation\AiTranslationService;
use App\Services\Translation\DTOs\TranslationResult;
use App\Services\Translation\DTOs\TranslationUnit;
use App\Services\Translation\Providers\GeminiTranslationProvider;
use App\Services\Translation\Providers\DeepSeekTranslationProvider;
use App\Services\Translation\TranslationValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AiTranslationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_multiple_strings_are_sent_in_one_batch_request(): void
    {
        Cache::flush();
        config([
            'translation.ai_enabled' => true,
            'translation.provider' => 'gemini',
            'translation.fallback_provider' => 'deepseek',
            'translation.batch_size' => 30,
            'translation_ai.google.api_key' => 'test-key',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => '["إضافة","الاسم","الحالة"]']]],
                    'finishReason' => 'STOP',
                ]],
            ], 200),
        ]);

        $translated = app(AiTranslationService::class)->translateBatch([
            'add' => 'Add batch-only label',
            'name' => 'Name batch-only label',
            'status' => 'Status batch-only label',
        ], 'ar', 'en');

        $this->assertSame(['add' => 'إضافة', 'name' => 'الاسم', 'status' => 'الحالة'], $translated);
        Http::assertSentCount(1);
    }

    public function test_active_languages_resolution()
    {
        $service = app(AiTranslationService::class);
        $languages = $service->getActiveLanguages();

        $this->assertIsArray($languages);
        $this->assertContains('en', $languages);
        $this->assertContains('ar', $languages);
    }

    public function test_translation_validator_placeholder_preservation()
    {
        $validator = new TranslationValidator();

        $source = "Welcome to {city_name}, visit :landmark today!";
        $validTranslation = "مرحباً بكم في {city_name}، قم بزيارة :landmark اليوم!";
        $invalidTranslation = "مرحباً بكم في القاهرة، قم بزيارة الأهرامات اليوم!";

        $this->assertTrue($validator->validatePlaceholders($source, $validTranslation));
        $this->assertFalse($validator->validatePlaceholders($source, $invalidTranslation));
    }

    public function test_translation_validator_untranslated_detection()
    {
        $validator = new TranslationValidator();

        $englishSource = "Luxor Day Tour to Karnak and Hatshepsut Temples";
        $untranslatedOutput = "Luxor Day Tour to Karnak and Hatshepsut Temples";
        $arabicOutput = "جولة لوكسر اليومية إلى معابد الكرنك وحتشبسوت";

        // Target: ar from en -> English text output is invalid
        $this->assertFalse($validator->isProperlyTranslated($englishSource, $untranslatedOutput, 'en', 'ar'));
        $this->assertTrue($validator->isProperlyTranslated($englishSource, $arabicOutput, 'en', 'ar'));
    }

    public function test_gemini_provider_builds_thinking_budget_zero_payload()
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [['text' => 'رحلة الأهرامات']]
                        ],
                        'finishReason' => 'STOP'
                    ]
                ],
                'usageMetadata' => [
                    'promptTokenCount' => 10,
                    'candidatesTokenCount' => 5,
                    'totalTokenCount' => 15
                ]
            ], 200)
        ]);

        config(['translation_ai.google.api_key' => 'test-key']);

        $gemini = app(GeminiTranslationProvider::class);
        $result = $gemini->translate("Pyramids Tour", "en", "ar");

        $this->assertTrue($result->isSuccess);
        $this->assertEquals('gemini', $result->provider);
        $this->assertEquals('رحلة الأهرامات', $result->translatedText);

        Http::assertSent(function ($request) {
            $body = json_decode($request->body(), true);
            return isset($body['generationConfig']['thinkingConfig']['thinkingBudget'])
                && $body['generationConfig']['thinkingConfig']['thinkingBudget'] === 0;
        });
    }

    public function test_deepseek_fallback_when_gemini_fails()
    {
        config([
            'translation_ai.google.api_key' => 'test-key',
            'translation_ai.deepseek.api_key' => 'test-deepseek-key'
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => 'Quota limit reached'], 429),
            'api.deepseek.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'جولة الأهرامات اليومية'
                        ]
                    ]
                ],
                'usage' => [
                    'prompt_tokens' => 12,
                    'completion_tokens' => 6,
                    'total_tokens' => 18
                ]
            ], 200)
        ]);

        $service = app(AiTranslationService::class);
        $unit = new TranslationUnit(
            entityType: 'package',
            entityId: 1,
            field: 'title',
            sourceLanguage: 'en',
            targetLanguage: 'ar',
            sourceText: 'Pyramids Day Tour'
        );

        $result = $service->translateUnit($unit);

        $this->assertTrue($result->isSuccess);
        $this->assertEquals('deepseek', $result->provider);
        $this->assertEquals('جولة الأهرامات اليومية', $result->translatedText);
    }

    public function test_manual_translations_are_protected()
    {
        $package = Package::create([
            'title' => ['en' => 'Cairo Nile Cruise', 'ar' => 'رحلة نيلية في القاهرة (يدوي)'],
            'description' => ['en' => 'Enjoy 3 nights on board.'],
            'package_type' => 'day_tour',
            'slug' => 'cairo-nile-cruise-' . uniqid(),
            'is_active' => true,
        ]);

        $schema = app(\App\Services\Translation\Schemas\PackageTranslationSchema::class);
        $units = $schema->extractUnits($package, 'en', ['ar'], true);

        // title in 'ar' is already set manually, so it must NOT be extracted
        $titleUnits = array_filter($units, fn($u) => $u->field === 'title');
        $this->assertEmpty($titleUnits);

        // description in 'ar' is missing, so it MUST be extracted
        $descUnits = array_filter($units, fn($u) => $u->field === 'description');
        $this->assertNotEmpty($descUnits);
    }

    public function test_dry_run_artisan_command()
    {
        Package::create([
            'title' => ['en' => 'Alexandria Day Trip'],
            'package_type' => 'day_tour',
            'slug' => 'alexandria-day-trip-' . uniqid(),
            'is_active' => true,
        ]);

        $this->artisan('translations:process-missing', [
            '--dry-run' => true,
        ])->assertExitCode(0);
    }
}
