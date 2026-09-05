<?php

namespace App\Traits;

use App\Models\Language;
use App\Services\TranslationService;

trait HandlesTranslatedFields
{
    protected function translateModelFields(array $data, array $fields): array
    {
        return app(TranslationService::class)->translateFields($data, $fields);
    }

    protected function applyTranslatedSearch($query, array $fields, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $locales = $this->translationLocales();

        return $query->where(function ($q) use ($fields, $locales, $term) {
            foreach ($fields as $field) {
                foreach ($locales as $locale) {
                    $q->orWhere($field . '->' . $locale, 'like', '%' . $term . '%');
                }

                $q->orWhere($field, 'like', '%' . $term . '%');
            }
        });
    }

    protected function translationLocales(): array
    {
        static $locales = null;

        if ($locales !== null) {
            return $locales;
        }

        if (class_exists(Language::class)) {
            $locales = Language::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->pluck('code')
                ->filter()
                ->values()
                ->all();
        }

        if (empty($locales)) {
            $locales = [
                config('app.locale', 'en'),
                config('app.fallback_locale', 'en'),
                'en',
                'ar',
            ];
        }

        return array_values(array_unique(array_filter($locales)));
    }
}
