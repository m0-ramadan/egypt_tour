<?php

namespace App\Support;

class LocaleNormalizer
{
    private const ALIASES = [
        'english' => 'en',
        'en-us' => 'en',
        'en_us' => 'en',
        'arabic' => 'ar',
        'ar-eg' => 'ar',
        'ar_eg' => 'ar',
    ];

    public function normalize(?string $locale, ?string $fallback = null): string
    {
        $locale = strtolower(trim((string) $locale));
        $locale = str_replace('_', '-', $locale);

        if (isset(self::ALIASES[$locale])) {
            return self::ALIASES[$locale];
        }

        if (preg_match('/^[a-z]{2,3}(?:-[a-z0-9]{2,8})?$/', $locale) === 1) {
            return explode('-', $locale, 2)[0];
        }

        if ($fallback !== null && strtolower(trim($fallback)) !== $locale) {
            return $this->normalize($fallback, 'en');
        }

        return 'en';
    }

    public function fromAcceptLanguage(?string $header, ?string $fallback = null): string
    {
        $first = trim(explode(',', (string) $header, 2)[0]);
        $first = trim(explode(';', $first, 2)[0]);

        return $this->normalize($first, $fallback ?? config('app.locale', 'en'));
    }

    public function normalizeList(array $locales): array
    {
        return array_values(array_unique(array_map(
            fn (mixed $locale): string => $this->normalize((string) $locale),
            $locales
        )));
    }
}
