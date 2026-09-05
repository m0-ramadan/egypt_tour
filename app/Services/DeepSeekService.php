<?php

namespace App\Services;

use App\Services\Translation\TranslationCircuitBreaker;
use App\Support\RateLimitedLogger;
use Illuminate\Support\Facades\Http;

class DeepSeekService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl;
    protected int $timeout;

    public function __construct(
        protected TranslationCircuitBreaker $circuitBreaker,
        protected RateLimitedLogger $logger
    )
    {
        $this->apiKey = (string) config('services.deepseek.api_key');
        $this->model = (string) config('services.deepseek.model', 'deepseek-chat');
        $this->baseUrl = (string) config('services.deepseek.base_url', 'https://api.deepseek.com/v1/chat/completions');
        $this->timeout = (int) config('services.deepseek.timeout', 60);
    }

    /**
     * Send plain prompt and get raw text response
     */
    public function ask(
        string $prompt,
        ?string $systemPrompt = null,
        float $temperature = 0.2,
        int $maxTokens = 2000
    ): ?string {
        $response = $this->sendRequest(
            prompt: $prompt,
            systemPrompt: $systemPrompt,
            temperature: $temperature,
            maxTokens: $maxTokens,
            jsonMode: false
        );

        if (!$response) {
            return null;
        }

        return $response['content'] ?? null;
    }

    /**
     * Send prompt and expect JSON object back
     */
    public function askJson(
        string $prompt,
        ?string $systemPrompt = null,
        float $temperature = 0.2,
        int $maxTokens = 2000
    ): ?array {
        $response = $this->sendRequest(
            prompt: $prompt,
            systemPrompt: $systemPrompt ?? 'Reply ONLY with valid JSON.',
            temperature: $temperature,
            maxTokens: $maxTokens,
            jsonMode: true
        );

        if (!$response || empty($response['content'])) {
            return null;
        }

        $content = $this->cleanJsonResponse($response['content']);
        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->warning(
                'deepseek-json-decode',
                'DeepSeek returned malformed JSON.',
                ['json_error' => json_last_error_msg()]
            );
            return null;
        }

        return $decoded;
    }

    /**
     * Generic request sender
     */
    protected function sendRequest(
        string $prompt,
        ?string $systemPrompt = null,
        float $temperature = 0.2,
        int $maxTokens = 2000,
        bool $jsonMode = false
    ): ?array {
        if (!(bool) config('translation.ai_enabled', true) || $this->circuitBreaker->isOpen('deepseek')) {
            return null;
        }

        if (empty($this->apiKey)) {
            $this->logger->warning(
                'deepseek-missing-api-key',
                'DeepSeek translation is unavailable because its API key is not configured.'
            );
            return null;
        }

        try {
            $payload = [
                'model' => $this->model,
                'messages' => array_values(array_filter([
                    $systemPrompt ? [
                        'role' => 'system',
                        'content' => $systemPrompt,
                    ] : null,
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ])),
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ];

            if ($jsonMode) {
                $payload['response_format'] = [
                    'type' => 'json_object',
                ];
            }

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl, $payload);

            if (!$response->successful()) {
                $this->circuitBreaker->recordFailure(
                    'deepseek',
                    $response->status(),
                    $response->json('message')
                );
                return null;
            }

            $content = $response->json('choices.0.message.content');

            if (!$content) {
                $this->logger->warning('deepseek-empty-content', 'DeepSeek returned empty content.');
                return null;
            }

            $this->circuitBreaker->recordSuccess('deepseek');

            return [
                'content' => trim($content),
                'raw' => $response->json(),
            ];
        } catch (\Throwable $e) {
            $this->circuitBreaker->recordFailure('deepseek', null, $e->getMessage());
            $this->logger->warning(
                'deepseek-request-exception',
                'DeepSeek request failed.',
                ['exception' => $e::class]
            );
            return null;
        }
    }

    /**
     * Remove markdown fences if model returns ```json ... ```
     */
    protected function cleanJsonResponse(string $content): string
    {
        $content = trim($content);
        $content = preg_replace('/^```json\s*/i', '', $content);
        $content = preg_replace('/^```\s*/i', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);

        return trim($content);
    }
}
