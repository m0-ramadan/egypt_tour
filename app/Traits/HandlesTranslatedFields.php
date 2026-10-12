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

        $locales = array_values(array_unique(array_filter(array_merge(
            $this->translationLocales(),
            ['en', 'ar', 'de', 'fr', config('app.locale', 'en')]
        ))));

        $grammar = $query->getQuery()->getGrammar();
        $driver = $query->getConnection()->getDriverName();
        $likePattern = '%' . $term . '%';

        return $query->where(function ($q) use ($fields, $locales, $grammar, $driver, $likePattern) {
            foreach ($fields as $field) {
                $wrappedField = $grammar->wrap($field);

                foreach ($locales as $locale) {
                    // Safe conditional extraction: only run JSON extraction if JSON_VALID is true
                    if ($driver === 'sqlite') {
                        $q->orWhereRaw("CASE WHEN json_valid({$wrappedField}) THEN json_extract({$wrappedField}, ?) ELSE NULL END LIKE ?", [
                            '$.' . $locale,
                            $likePattern,
                        ]);
                    } else {
                        $q->orWhereRaw("CASE WHEN JSON_VALID({$wrappedField}) THEN JSON_UNQUOTE(JSON_EXTRACT({$wrappedField}, ?)) ELSE NULL END LIKE ?", [
                            '$.' . $locale,
                            $likePattern,
                        ]);
                    }
                }

                // Also match plain text or raw string columns directly
                $q->orWhere($field, 'like', $likePattern);
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
