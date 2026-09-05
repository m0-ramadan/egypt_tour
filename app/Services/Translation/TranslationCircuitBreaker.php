<?php

namespace App\Services\Translation;

use App\Support\RateLimitedLogger;
use Illuminate\Support\Facades\Cache;

class TranslationCircuitBreaker
{
    public function __construct(private readonly RateLimitedLogger $logger) {}

    public function isOpen(string $provider): bool
    {
        if (!(bool) config('translation.ai_enabled', true)) {
            return true;
        }

        try {
            return Cache::has($this->key($provider));
        } catch (\Throwable) {
            return false;
        }
    }

    public function recordFailure(string $provider, ?int $status = null, ?string $reason = null): void
    {
        if (!$this->shouldOpen($status, $reason)) {
            return;
        }

        $cooldown = (int) config('translation.failure_cooldown', 3600);

        try {
            Cache::put($this->key($provider), [
                'status' => $status,
                'opened_at' => now()->toIso8601String(),
            ], $cooldown);
        } catch (\Throwable) {
            // A cache outage must not make translation a runtime dependency.
        }

        $this->logger->warning(
            "translation-circuit:{$provider}",
            ucfirst($provider) . ' translation is temporarily unavailable; circuit breaker opened.',
            array_filter(['status' => $status, 'cooldown_seconds' => $cooldown]),
            $cooldown
        );
    }

    public function recordSuccess(string $provider): void
    {
        try {
            Cache::forget($this->key($provider));
        } catch (\Throwable) {
            // Nothing to recover here; the successful response can still be used.
        }
    }

    private function shouldOpen(?int $status, ?string $reason): bool
    {
        if (in_array($status, [401, 402, 403, 429], true) || ($status !== null && $status >= 500)) {
            return true;
        }

        if ($status === null && $reason !== null) {
            $reason = strtolower($reason);

            return str_contains($reason, 'balance')
                || str_contains($reason, 'quota')
                || str_contains($reason, 'timeout')
                || str_contains($reason, 'connection');
        }

        return false;
    }

    private function key(string $provider): string
    {
        return 'translation:circuit:' . strtolower(trim($provider));
    }
}
