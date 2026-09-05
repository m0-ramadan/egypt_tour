<?php

namespace App\Services;

use App\Services\Translation\AiTranslationService;
use App\Services\Translation\TranslationKeyValidator;
use App\Support\LocaleNormalizer;

/**
 * Backward-compatible adapter for legacy callers.
 * Runtime translation and file writes are intentionally disabled by default.
 */
class DeepSeekTranslationService
{
    public function __construct(
        private readonly AiTranslationService $translationService,
        private readonly TranslationKeyValidator $keyValidator,
        private readonly LocaleNormalizer $localeNormalizer
    ) {}

    public function translateAndSave(string $key, string $locale): string
    {
        if (!(bool) config('translation.runtime_auto_translate', false) || !$this->keyValidator->isValid($key)) {
            return $key;
        }

        return $this->translationService->translateString(
            $key,
            $this->localeNormalizer->normalize($locale),
            $this->localeNormalizer->normalize((string) config('translation.source_locale', 'en'))
        );
    }
}
