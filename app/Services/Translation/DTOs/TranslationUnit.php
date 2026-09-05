<?php

namespace App\Services\Translation\DTOs;

class TranslationUnit
{
    public string $contentHash;

    public function __construct(
        public string $entityType,
        public string|int|null $entityId,
        public string $field,
        public string $sourceLanguage,
        public string $targetLanguage,
        public string $sourceText,
        public string $structuredType = 'text', // text, json_array, faq_json, html
        ?string $contentHash = null
    ) {
        $this->contentHash = $contentHash ?: self::calculateHash($sourceLanguage, $targetLanguage, $sourceText, $structuredType);
    }

    public static function calculateHash(string $sourceLang, string $targetLang, string $text, string $type = 'text'): string
    {
        $normalized = trim(preg_replace('/\s+/', ' ', $text));
        return hash('sha256', "{$sourceLang}:{$targetLang}:{$type}:{$normalized}");
    }
}
