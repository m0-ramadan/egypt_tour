<?php

namespace App\Services\Translation\DTOs;

class TranslationOptions
{
    public function __construct(
        public ?int $maxOutputTokens = null,
        public float $temperature = 0.1,
        public string $structuredType = 'text', // text, json_array, faq_json, html
        public bool $preserveFormatting = true,
        public array $extraOptions = []
    ) {}
}
