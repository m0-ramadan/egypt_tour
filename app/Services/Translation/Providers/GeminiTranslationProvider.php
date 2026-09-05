<?php

namespace App\Services\Translation\Providers;

use App\Contracts\TranslationProviderInterface;
use App\Services\Translation\DTOs\TranslationOptions;
use App\Services\Translation\DTOs\TranslationResult;
use App\Services\Translation\TranslationCircuitBreaker;
use App\Support\RateLimitedLogger;
use Illuminate\Support\Facades\Http;

class GeminiTranslationProvider implements TranslationProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected int $timeout;
    protected string $endpoint;

    public function __construct(
        protected TranslationCircuitBreaker $circuitBreaker,
        protected RateLimitedLogger $logger
    )
    {
        $this->apiKey = (string) config('translation_ai.google.api_key');
        $this->model = (string) config('translation_ai.google.model', 'gemini-2.5-flash');
        $this->timeout = (int) config('translation_ai.google.timeout', 30);
        $this->endpoint = (string) config('translation_ai.google.endpoint', 'https://generativelanguage.googleapis.com/v1beta/models');
    }

    public function getName(): string
    {
        return 'gemini';
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
                'Gemini circuit breaker is open.'
            );
        }

        if (empty(trim($this->apiKey))) {
            $this->logger->warning(
                'gemini-missing-api-key',
                'Gemini translation is unavailable because its API key is not configured.'
            );
            return TranslationResult::failure(
                $this->getName(),
                $this->getModel(),
                'Google AI API key is missing in environment config.'
            );
        }

        $prompt = $this->buildPrompt($text, $sourceLanguage, $targetLanguage, $options->structuredType);
        $maxOutputTokens = $options->maxOutputTokens ?: $this->estimateOutputTokenLimit($text);

        $url = rtrim($this->endpoint, '/') . "/{$this->model}:generateContent";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'thinkingConfig' => [
                    'thinkingBudget' => 0, // Mandatory 0 to disable thinking tokens
                ],
                'maxOutputTokens' => $maxOutputTokens,
                'temperature' => $options->temperature,
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->apiKey,
            ])
            ->timeout($this->timeout)
            ->post($url, $payload);

            $durationMs = (int) (round(microtime(true) - $startTime, 3) * 1000);

            if (!$response->successful()) {
                $status = $response->status();
                $this->circuitBreaker->recordFailure($this->getName(), $status, $response->json('message'));

                return TranslationResult::failure(
                    $this->getName(),
                    $this->getModel(),
                    "Gemini HTTP {$status}",
                    $durationMs,
                    $status
                );
            }

            $data = $response->json();
            $translatedText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $finishReason = $data['candidates'][0]['finishReason'] ?? null;

            if ($finishReason === 'MAX_TOKENS') {
                $this->logger->warning(
                    'gemini-max-tokens',
                    'Gemini translation was truncated by its output token limit.'
                );
            }

            $usage = $data['usageMetadata'] ?? [];
            $promptTokens = $usage['promptTokenCount'] ?? null;
            $outputTokens = $usage['candidatesTokenCount'] ?? null;
            $totalTokens = $usage['totalTokenCount'] ?? null;

            if (empty(trim($translatedText))) {
                return TranslationResult::failure(
                    $this->getName(),
                    $this->getModel(),
                    'Empty response from Gemini',
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
                'gemini-provider-exception',
                'Gemini translation request failed.',
                ['exception' => $e::class]
            );

            return TranslationResult::failure(
                $this->getName(),
                $this->getModel(),
                'Gemini request failed.',
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
