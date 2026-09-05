<?php

namespace Tests\Unit;

use App\Services\Translation\TranslationKeyValidator;
use App\Services\Translation\TranslationValidator;
use PHPUnit\Framework\TestCase;

class TranslationSafetyTest extends TestCase
{
    public function test_scanner_rejects_code_but_keeps_short_ui_labels(): void
    {
        $validator = new TranslationKeyValidator();

        foreach (["'desktop', ])", 'mobile', 'forceDelete', 'uploads/', 'users', 'App\\Models\\User'] as $invalid) {
            $this->assertFalse($validator->isValid($invalid), $invalid);
        }

        foreach (['Add', 'Name', 'Status', 'إضافة'] as $valid) {
            $this->assertTrue($validator->isValid($valid), $valid);
        }
    }

    public function test_invalid_ai_json_and_executable_output_are_rejected(): void
    {
        $validator = new TranslationValidator();

        $this->assertFalse($validator->validateJsonArray('["One"]', '["واحد","extra"]'));
        $this->assertFalse($validator->validateSafeOutput('Welcome :name', '<?php echo :name;'));
        $this->assertFalse($validator->validatePlaceholders('Welcome :name', 'مرحبًا'));
        $this->assertTrue($validator->validatePlaceholders('Welcome :name', 'مرحبًا :name'));
    }
}
