<?php

namespace Tests\Feature;

use App\Services\Translation\Providers\DeepSeekTranslationProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DeepSeekCircuitBreakerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config([
            'translation.ai_enabled' => true,
            'translation.failure_cooldown' => 3600,
            'translation_ai.deepseek.api_key' => 'test-key',
            'translation_ai.deepseek.api_url' => 'https://api.deepseek.com/v1/chat/completions',
        ]);
    }

    public function test_402_opens_circuit_and_prevents_a_second_request(): void
    {
        Http::fake([
            'api.deepseek.com/*' => Http::response(['error' => ['message' => 'Insufficient Balance']], 402),
        ]);

        $provider = app(DeepSeekTranslationProvider::class);
        $first = $provider->translate('Welcome :name', 'en', 'ar');
        $second = $provider->translate('Another string', 'en', 'ar');

        $this->assertFalse($first->isSuccess);
        $this->assertSame(402, $first->httpStatus);
        $this->assertFalse($second->isSuccess);
        $this->assertStringContainsString('circuit breaker', strtolower((string) $second->errorMessage));
        Http::assertSentCount(1);
    }
}
