<?php

namespace Tests\Unit;

use App\Http\Middleware\TranslateAdminHtml;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class TranslateAdminHtmlTest extends TestCase
{
    public function test_it_preserves_dynamic_markup_inside_scripts_while_translating_html(): void
    {
        $script = <<<'HTML'
<script>
const item = `<div class="item"><span>01</span><input type="text"><textarea></textarea></div>`;
</script>
HTML;
        $html = '<!doctype html><html><body><p>Hello</p>' . $script . '</body></html>';

        $method = new ReflectionMethod(TranslateAdminHtml::class, 'translateHtml');
        $translated = $method->invoke(new TranslateAdminHtml(), $html, ['Hello' => 'مرحبًا']);

        $this->assertStringContainsString('<p>مرحبًا</p>', $translated);
        $this->assertStringContainsString($script, $translated);
        $this->assertStringContainsString('</span><input type="text"><textarea></textarea></div>', $translated);
    }
}
