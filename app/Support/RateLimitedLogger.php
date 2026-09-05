<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RateLimitedLogger
{
    private static array $loggedThisProcess = [];

    public function warning(string $key, string $message, array $context = [], ?int $seconds = null): void
    {
        $this->writeOnce('warning', $key, $message, $context, $seconds);
    }

    public function error(string $key, string $message, array $context = [], ?int $seconds = null): void
    {
        $this->writeOnce('error', $key, $message, $context, $seconds);
    }

    public function debug(string $key, string $message, array $context = [], ?int $seconds = null): void
    {
        $this->writeOnce('debug', $key, $message, $context, $seconds);
    }

    private function writeOnce(string $level, string $key, string $message, array $context, ?int $seconds): void
    {
        $cacheKey = 'rate-limited-log:' . hash('sha256', $key);
        $seconds ??= (int) config('translation.log_cooldown', 3600);

        try {
            if (!Cache::add($cacheKey, true, max(60, $seconds))) {
                return;
            }
        } catch (\Throwable) {
            if (isset(self::$loggedThisProcess[$cacheKey])) {
                return;
            }

            self::$loggedThisProcess[$cacheKey] = true;
        }

        Log::log($level, $message, $context);
    }
}
