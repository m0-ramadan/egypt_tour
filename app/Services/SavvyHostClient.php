<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SavvyHostClient
{
    public function __construct(
        protected SavvyHostAuthService $authService
    ) {}

    /**
     * Send GET request to SavvyHost API.
     */
    public function get(string $uri, array $query = []): Response
    {
        return $this->request('GET', $uri, ['query' => $query]);
    }

    /**
     * Send POST request to SavvyHost API.
     */
    public function post(string $uri, array $payload = []): Response
    {
        return $this->request('POST', $uri, ['json' => $payload]);
    }

    /**
     * Execute HTTP request with automatic 401 authentication token refresh and single retry (Rules #9, #10, #13).
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     * @param bool $canRetryAuth
     * @return Response
     * @throws Exception
     */
    public function request(string $method, string $uri, array $options = [], bool $canRetryAuth = true): Response
    {
        $baseUrl = config('services.savvyhost.base_url', 'https://api.savvyhost.net');
        $tenant = config('services.savvyhost.tenant', 'etrotours');
        $url = str_starts_with($uri, 'http') ? $uri : (rtrim($baseUrl, '/') . '/' . ltrim($uri, '/'));

        // 1. Get valid token (cached or fresh)
        $token = $this->authService->getValidToken();

        $headers = array_merge([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
            'x-tenant-subdomain' => $tenant,
        ], $options['headers'] ?? []);

        $timeout = $options['timeout'] ?? 30;

        // 2. Build HTTP request
        $httpRequest = Http::withHeaders($headers)
            ->timeout($timeout)
            ->retry(3, 500, function ($exception) {
                return $exception instanceof ConnectionException;
            }, throw: false);

        if (!empty($options['query'])) {
            $httpRequest->withQueryParameters($options['query']);
        }

        try {
            $response = match (strtoupper($method)) {
                'POST' => $httpRequest->post($url, $options['json'] ?? []),
                'PUT' => $httpRequest->put($url, $options['json'] ?? []),
                'DELETE' => $httpRequest->delete($url, $options['json'] ?? []),
                default => $httpRequest->get($url, $options['query'] ?? []),
            };
        } catch (Exception $e) {
            Log::error("SavvyHost API HTTP connection exception on {$uri}");
            throw new Exception("Failed to connect to SavvyHost API endpoint.");
        }

        // 3. Handle 401 Unauthorized (Rule #9 & #10)
        if ($response->status() === 401) {
            if ($canRetryAuth) {
                Log::warning("SavvyHost API returned 401 Unauthorized for {$uri}. Refreshing token and retrying request...");
                $this->authService->invalidateToken();

                // Force refresh token
                $this->authService->getValidToken(forceRefresh: true);

                // Single auto-retry
                return $this->request($method, $uri, $options, canRetryAuth: false);
            }

            Log::error("SavvyHost API authentication failed after token refresh (HTTP 401) for {$uri}.");
            throw new Exception('SavvyHost authentication failed after token refresh.');
        }

        return $response;
    }

    /**
     * Helper check if string starts with http
     */
    protected function isFullUrl(string $url): bool
    {
        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://');
    }
}
