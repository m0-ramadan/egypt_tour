<?php

namespace App\Contracts;

use App\Services\Translation\DTOs\TranslationOptions;
use App\Services\Translation\DTOs\TranslationResult;

interface TranslationProviderInterface
{
    /**
     * Get provider identifier key (e.g. gemini, deepseek).
     */
    public function getName(): string;

    /**
     * Get model identifier key.
     */
    public function getModel(): string;

    /**
     * Translate text from source language to target language.
     */
    public function translate(
        string $text,
        string $sourceLanguage,
        string $targetLanguage,
        ?TranslationOptions $options = null
    ): TranslationResult;
}
