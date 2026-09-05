<?php

namespace App\Services\Translation\Providers;

use App\Contracts\TranslationProviderInterface;
use App\Services\Translation\DTOs\TranslationOptions;
use App\Services\Translation\DTOs\TranslationResult;
use App\Services\Translation\TranslationCircuitBreaker;
use App\Support\RateLimitedLogger;
use Illuminate\Support\Facades\Http;

class DeepSeekTranslationProvider implements TranslationProviderInterface
{
    protected string $apiKey;
    protected string $apiUrl;
    protected string $model;
    protected int $timeout;

    public function __construct(
        protected TranslationCircuitBreaker $circuitBreaker,
        protected RateLimitedLogger $logger
    )
    {
        $this->apiKey = (string) config('translation_ai.deepseek.api_key');
        $this->apiUrl = (string) config('translation_ai.deepseek.api_url', 'https://api.deepseek.com/chat/completions');
        $this->model = (string) config('translation_ai.deepseek.model', 'deepseek-chat');
        $this->timeout = (int) config('translation_ai.deepseek.timeout', 30);
    }

    public function getName(): string
    {
        return 'deepseek';
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function translate(
        string $text,
        string $sourceLanguage,
        string $targetLanguage,
        ?TranslationOptions $options = null
    ): TranslationResult {
        $startTime = microtime(true);
        $options = $options ?? new TranslationOptions();

        if ($this->circuitBreaker->isOpen($this->getName())) {
            return TranslationResult::failure(
                $this->getName(),
                $this->getModel(),
                'DeepSeek circuit breaker is open.'
            );
        }

        if (empty(trim($this->apiKey))) {
            $this->logger->warning(
                'deepseek-missing-api-key',
                'DeepSeek translation is unavailable because its API key is not configured.'
            );
            return TranslationResult::failure(
                $this->getName(),
                $this->getModel(),
                'DeepSeek API key is missing in environment config.'
            );
        }

        $prompt = $this->buildPrompt($text, $sourceLanguage, $targetLanguage, $options->structuredType);
        $maxTokens = $options->maxOutputTokens ?: $this->estimateOutputTokenLimit($text);

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $options->temperature,
            'max_tokens' => $maxTokens,
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->timeout($this->timeout)
            ->post($this->apiUrl, $payload);

            $durationMs = (int) (round(microtime(true) - $startTime, 3) * 1000);

            if (!$response->successful()) {
                $status = $response->status();
                $this->circuitBreaker->recordFailure($this->getName(), $status, $response->json('message'));

                return TranslationResult::failure(
                    $this->getName(),
                    $this->getModel(),
                    "DeepSeek HTTP {$status}",
                    $durationMs,
                    $status
                );
            }

            $data = $response->json();
            $translatedText = $data['choices'][0]['message']['content'] ?? '';

            $usage = $data['usage'] ?? [];
            $promptTokens = $usage['prompt_tokens'] ?? null;
            $outputTokens = $usage['completion_tokens'] ?? null;
            $totalTokens = $usage['total_tokens'] ?? null;

            if (empty(trim($translatedText))) {
                return TranslationResult::failure(
                    $this->getName(),
                    $this->getModel(),
                    'Empty response from DeepSeek',
                    $durationMs
                );
            }

            $this->circuitBreaker->recordSuccess($this->getName());

            return new TranslationResult(
                translatedText: trim($translatedText),
                provider: $this->getName(),
                model: $this->getModel(),
                isSuccess: true,
                errorMessage: null,
                promptTokens: $promptTokens,
                outputTokens: $outputTokens,
                totalTokens: $totalTokens,
                durationMs: $durationMs
            );
        } catch (\Throwable $e) {
            $durationMs = (int) (round(microtime(true) - $startTime, 3) * 1000);
            $this->circuitBreaker->recordFailure($this->getName(), null, $e->getMessage());
            $this->logger->warning(
                'deepseek-provider-exception',
                'DeepSeek translation request failed.',
                ['exception' => $e::class]
            );

            return TranslationResult::failure(
                $this->getName(),
                $this->getModel(),
                'DeepSeek request failed.',
                $durationMs
            );
        }
    }

    public function estimateOutputTokenLimit(string $text): int
    {
        $charLen = mb_strlen($text);
        $approxSourceTokens = (int) ceil($charLen / 4.0);
        $limit = (int) ceil($approxSourceTokens * 1.6) + 128;

        return max(256, min($limit, 4096));
    }

    protected function buildPrompt(string $text, string $sourceLang, string $targetLang, string $structuredType): string
    {
        $srcName = strtoupper($sourceLang);
        $tgtName = strtoupper($targetLang);

        if ($structuredType === 'faq_json') {
            return "Translate JSON string values from {$srcName} to {$tgtName}.\n"
                 . "Keep keys and JSON structure unchanged. Preserve placeholders ({var}, :val) and numbers.\n"
                 . "Return valid JSON only without markdown formatting.\n"
                 . "JSON:\n" . $text;
        }

        if ($structuredType === 'json_array') {
            return "Translate string values in JSON array from {$srcName} to {$tgtName}.\n"
                 . "Keep array order and structure. Return valid JSON array only.\n"
                 . "JSON:\n" . $text;
        }

        if ($structuredType === 'html') {
            return "Translate text nodes from {$srcName} to {$tgtName}.\n"
                 . "Preserve all HTML tags, attributes, placeholders and structure. Return translated HTML only.\n"
                 . "TEXT:\n" . $text;
        }

        return "Translate from {$srcName} to {$tgtName}.\n"
             . "Preserve meaning, names, numbers, HTML tags and placeholders. Return only the translation.\n"
             . "TEXT:\n" . $text;
    }
}
