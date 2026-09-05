<?php

namespace App\Translation;

use Illuminate\Translation\Translator as BaseTranslator;
use App\Services\DeepSeekTranslationService;

class DeepSeekTranslator extends BaseTranslator
{
    /**
     * Get the translation for the given key.
     *
     * @param  string  $key
     * @param  array   $replace
     * @param  string|null  $locale
     * @param  bool  $fallback
     * @return string|array
     */
    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        $result = parent::get($key, $replace, $locale, $fallback);
        $autoTranslateMissing = (bool) config('translation.runtime_auto_translate', false);

        // If the translation was not found, the translator returns the original key.
        // We only trigger DeepSeek if:
        // 1. The result exactly matches the key
        // 2. We're requesting a locale other than the fallback/default locale (e.g. English)
        
        $targetLocale = $locale ?: $this->locale;

        if ($autoTranslateMissing && $result === $key && $targetLocale !== $this->fallback) {
            $translated = app(DeepSeekTranslationService::class)->translateAndSave($key, $targetLocale);
            
            if ($translated !== $key) {
                // If translation succeeded, reload the language files so it works immediately next time.
                // For the current request, we just apply the replacements to the returned string.
                $this->loaded = []; // clear cache to reload JSONs if needed, though this is for subsequent calls.
                return $this->makeReplacements($translated, $replace);
            }
        }

        return $result;
    }
}
