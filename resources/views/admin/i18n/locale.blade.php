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

    if (!function_exists('admin_arabic_fallback_translation')) {
        function admin_arabic_fallback_translation(string $text): string
        {
            if (!preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
                return $text;
            }

            static $dict = [
                'Dashboard' => 'Dashboard',
                'Manage website FAQs' => 'Manage',
                'Add' => 'Add',
                'Edit' => 'Edit',
                'View' => 'View',
                'Delete' => 'Delete',
                'Back' => 'Back',
                'Save' => 'Save',
                'Cancel' => 'Cancel',
                'Update' => 'Update',
                'Confirm' => 'Confirm',
                'Enabled' => 'Enabled',
                'Disabled' => 'Disabled',
                'Active' => 'Active',
                'Inactive' => 'Inactive',
                'Active' => 'Active',
                'Inactive' => 'Inactive',
                'Published' => 'Published',
                'Unpublished' => 'Unpublished',
                'Featured' => 'Featured',
                'Standard' => 'Standard',
                'No Name' => 'No Name',
                'Without Title' => 'No Title',
                'None SEO Description' => 'No Description',
                'Without Reference' => 'No Reference',
                'No Content' => 'No Content',
                'No There is' => 'None',
                'Status' => 'Status',
                'Actions' => 'Actions',
                'Actions' => 'Actions',
                'Name' => 'Name',
                'Name' => 'Name',
                'Name' => 'Name',
                'Description' => 'Description',
                'Title' => 'Title',
                'Image' => 'Image',
                'Sort Order' => 'Sort Order',
                'Created At' => 'Created At',
                'Updated At' => 'Updated At',
                'Category' => 'Category',
                'City' => 'City',
                'Country' => 'Country',
                'Price' => 'Price',
                'Currency' => 'Currency',
                'Search' => 'Search',
                'Users' => 'Users',
                'Clients' => 'Clients',
                'Client' => 'Client',
                'Subscribers' => 'Subscribers',
                'Messages' => 'Messages',
                'Settings' => 'Settings',
                'Permissions' => 'Permissions',
                'Roles' => 'Roles',
                'Trips' => 'Packages',
                'Packages' => 'Packages',
                'Articles' => 'Articles',
                'Pages' => 'Pages',
                'FAQs' => 'FAQs',
                'Menus' => 'Menus',
                'Attractions' => 'Attractions',
                'Attractions' => 'Attractions',
                'Stores' => 'Stores',
                'Payments' => 'Payments',
                'Admins' => 'Admins',
                'Admin' => 'Admin',
                'Admins' => 'Admins',
                'Merchants' => 'Merchants',
                'Accounts' => 'Accounts',
                'Create' => 'Create',
                'Article' => 'Article',
                'with AI AI' => 'with AI',
                'AI AI' => 'AI',
                'with AI' => 'with AI',
                'AI' => 'AI',
            ];

            if (isset($dict[$text])) {
                return $dict[$text];
            }

            $words = explode(' ', $text);
            $res = [];
            foreach ($words as $w) {
                $res[] = $dict[$w] ?? $w;
            }
            return implode(' ', $res);
        }
    }

    if (!function_exists('admin_t')) {
        function admin_t($key, array $replace = []): string
        {
            $key = trim((string) $key);
            $locale = app()->getLocale();
            $maps = admin_translation_maps();

            $translated = null;

            if ($locale === 'en') {
                if (isset($maps['en'][$key])) {
                    $val = $maps['en'][$key];
                    if (preg_match('/[\x{0600}-\x{06FF}]/u', $val) && !preg_match('/[\x{0600}-\x{06FF}]/u', $key)) {
                        $translated = $key;
                    } else {
                        $translated = $val;
                    }
                } else {
                    $cleanKey = trim(preg_replace('/^[\'"]+|[\'"]+$/', '', $key));
                    if (isset($maps['en'][$cleanKey])) {
                        $translated = $maps['en'][$cleanKey];
                    }
                }
            } else {
                $translated = $maps[$locale][$key] ?? null;
            }

            if ($translated === null || preg_match('/[\x{0600}-\x{06FF}]/u', $translated)) {
                $translated = admin_arabic_fallback_translation($key);
            }

            foreach ($replace as $name => $value) {
                $translated = str_replace(':' . $name, (string) $value, $translated);
            }
            return $translated;
        }
    }
@endphp
