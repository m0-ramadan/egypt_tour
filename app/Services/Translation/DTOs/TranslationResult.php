<?php

namespace App\Services\Translation\DTOs;

class TranslationResult
{
    public function __construct(
        public string $translatedText,
        public string $provider,
        public string $model,
        public bool $isSuccess = true,
        public ?string $errorMessage = null,
        public ?int $promptTokens = null,
        public ?int $outputTokens = null,
        public ?int $totalTokens = null,
        public int $durationMs = 0,
        public ?int $httpStatus = null
    ) {}

    public static function failure(
        string $provider,
        string $model,
        string $errorMessage,
        int $durationMs = 0,
        ?int $httpStatus = null
    ): self
    {
        return new self(
            translatedText: '',
            provider: $provider,
            model: $model,
            isSuccess: false,
            errorMessage: $errorMessage,
            durationMs: $durationMs,
            httpStatus: $httpStatus
        );
    }
}
