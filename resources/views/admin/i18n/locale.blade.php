@php
    $resolvedAdminLocale = 'en';
    session(['admin_locale' => $resolvedAdminLocale]);
    app()->setLocale($resolvedAdminLocale);

    if (!function_exists('admin_translation_maps')) {
        function admin_translation_maps(): array
        {
            static $maps;
            if ($maps === null) {
                $maps = require resource_path('views/admin/i18n/translations.php');
            }
            return $maps;
        }
    }

    if (!function_exists('admin_t')) {
        function admin_t($key, array $replace = []): string
        {
            $key = (string) $key;
            $locale = app()->getLocale();
            $maps = admin_translation_maps();

            if ($locale === 'en') {
                if (isset($maps['en'][$key])) {
                    $val = $maps['en'][$key];
                    if (preg_match('/[\x{0600}-\x{06FF}]/u', $val) && !preg_match('/[\x{0600}-\x{06FF}]/u', $key)) {
                        $translated = $key;
                    } else {
                        $translated = $val;
                    }
                } else {
                    $translated = $key;
                }
            } else {
                $translated = $maps[$locale][$key] ?? $key;
            }

            foreach ($replace as $name => $value) {
                $translated = str_replace(':' . $name, (string) $value, $translated);
            }
            return $translated;
        }
    }
@endphp
