<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SavvyHostAuthService
{
    /**
     * Safety margin in seconds before token expiration (5 minutes).
     */
    protected const SAFETY_MARGIN_SECONDS = 300;

    /**
     * Get a valid Bearer token for SavvyHost API requests.
     *
     * @param bool $forceRefresh
     * @return string
     * @throws Exception
     */
    public function getValidToken(bool $forceRefresh = false): string
    {
        $tenant = $this->getTenant();
        $cacheTokenKey = $this->getTokenCacheKey();
        $cacheExpiresKey = $this->getExpiresCacheKey();

        if (!$forceRefresh) {
            $token = Cache::get($cacheTokenKey);
            if ($token && !$this->isTokenExpired()) {
                return $token;
            }
        }

        // Concurrency Lock to prevent stampede (Rule #14)
        $lockKey = "savvyhost:auth:refresh-lock:{$tenant}";
        $lock = Cache::lock($lockKey, 10);

        try {
            return $lock->block(5, function () use ($cacheTokenKey, $cacheExpiresKey, $forceRefresh) {
                // Double-check cache after acquiring lock
                if (!$forceRefresh) {
                    $token = Cache::get($cacheTokenKey);
                    if ($token && !$this->isTokenExpired()) {
                        return $token;
                    }
                }

                // Check if email and password are configured
                $email = config('services.savvyhost.email');
                $password = config('services.savvyhost.password');

                if (!empty($email) && !empty($password)) {
                    return $this->login();
                }

                // Fallback to initial SAVVYHOST_API_TOKEN if configured (Rule #8)
                $fallbackToken = config('services.savvyhost.token');
                if (!empty($fallbackToken)) {
                    $fallbackToken = $this->sanitizeToken($fallbackToken);

                    if ($forceRefresh) {
                        throw new Exception('SavvyHost API token is invalid or expired (HTTP 401). Please set SAVVYHOST_LOGIN_EMAIL and SAVVYHOST_LOGIN_PASSWORD in your .env file to enable automatic token refresh.');
                    }

                    // Cache fallback token initially
                    Cache::put($cacheTokenKey, $fallbackToken, 86400);
                    Cache::put($cacheExpiresKey, now()->timestamp + 86400, 86400);

                    return $fallbackToken;
                }

                throw new Exception('SavvyHost authentication failed: Please set SAVVYHOST_LOGIN_EMAIL and SAVVYHOST_LOGIN_PASSWORD (or SAVVYHOST_API_TOKEN) in your .env file.');
            });
        } catch (Exception $e) {
            Log::error('SavvyHostAuthService token retrieval failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Execute login request to SavvyHost login API.
     *
     * @return string Token
     * @throws Exception
     */
    public function login(): string
    {
        $baseUrl = config('services.savvyhost.base_url', 'https://api.savvyhost.net');
        $tenant = $this->getTenant();
        $email = config('services.savvyhost.email');
        $password = config('services.savvyhost.password');

        if (empty($email) || empty($password)) {
            throw new Exception('SavvyHost login credentials (email/password) are missing.');
        }

        $endpoint = rtrim($baseUrl, '/') . '/api/v1/dashboard/login';

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'x-tenant-subdomain' => $tenant,
            ])
            ->timeout(15)
            ->post($endpoint, [
                'email' => $email,
                'password' => $password,
            ]);
        } catch (Exception $e) {
            Log::error('SavvyHost login connection error');
            throw new Exception('Failed to connect to SavvyHost authentication service.');
        }

        if (!$response->successful()) {
            $status = $response->status();
            Log::error("SavvyHost login request failed with HTTP status {$status}");
            throw new Exception('SavvyHost authentication failed. Please verify credentials.');
        }

        $json = $response->json();
        $token = $json['data']['token'] ?? $json['token'] ?? null;

        if (empty($token) || !is_string($token)) {
            Log::error('SavvyHost login response did not contain a valid token');
            throw new Exception('Invalid response format from SavvyHost authentication service.');
        }

        $token = $this->sanitizeToken($token);

        // Expiration handling (Rule #5 & #6)
        $expiresIn = $json['data']['expires_in'] ?? $json['expires_in'] ?? 86400; // default 24h
        $expiresAt = $json['data']['expires_at'] ?? null;

        if ($expiresAt) {
            $expireTimestamp = strtotime((string) $expiresAt);
        } else {
            $expireTimestamp = now()->timestamp + (int) $expiresIn;
        }

        $cacheTtl = max(60, $expireTimestamp - now()->timestamp - self::SAFETY_MARGIN_SECONDS);

        Cache::put($this->getTokenCacheKey(), $token, $cacheTtl);
        Cache::put($this->getExpiresCacheKey(), $expireTimestamp, $cacheTtl);

        Log::info('SavvyHost token successfully refreshed and cached.');

        return $token;
    }

    /**
     * Invalidate cached token.
     */
    public function invalidateToken(): void
    {
        Cache::forget($this->getTokenCacheKey());
        Cache::forget($this->getExpiresCacheKey());
        Log::info('SavvyHost cached token invalidated.');
    }

    /**
     * Check if cached token is expired or close to expiration (within safety margin).
     */
    public function isTokenExpired(): bool
    {
        $expiresAt = Cache::get($this->getExpiresCacheKey());
        if (!$expiresAt) {
            return true;
        }

        return now()->timestamp >= ((int) $expiresAt - self::SAFETY_MARGIN_SECONDS);
    }

    /**
     * Get tenant subdomain from config.
     */
    public function getTenant(): string
    {
        return config('services.savvyhost.tenant', 'etrotours');
    }

    public function getTokenCacheKey(): string
    {
        return 'savvyhost:auth:token:' . $this->getTenant();
    }

    public function getExpiresCacheKey(): string
    {
        return 'savvyhost:auth:expires_at:' . $this->getTenant();
    }

    /**
     * Sanitize raw token string to strip pre-existing "Bearer " prefix.
     */
    public function sanitizeToken(string $token): string
    {
        return preg_replace('/^Bearer\s+/i', '', trim($token));
    }
}
