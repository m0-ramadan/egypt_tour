<?php

if (!function_exists('get_user_image')) {
    /**
     * إرجاع رابط الصورة الصحيح للمستخدم
     *
     * @param string|null $image
     * @return string
     */
    function get_user_image(?string $image): string
    {
        if (!$image) {
            return config('app.default_user_image', "https://static.vecteezy.com/system/resources/previews/011/209/565/non_2x/user-profile-avatar-free-vector.jpg"); // لا توجد صورة
        }

        // إذا الرابط موجود بالفعل كـ https أو http
        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }
        // الرابط نسبي في قاعدة البيانات
        return asset('storage/' . $image);
    }
}


if (!function_exists('get_product_image')) {
    /**
     * إرجاع رابط الصورة الصحيح للمنتج
     *
     * @param string|null $image
     * @return string
     */
    function get_product_image(?string $image): string
    {
        // صورة افتراضية للمنتج
        if (!$image) {
            return config(
                'app.default_product_image',
                'https://static.vecteezy.com/system/resources/previews/002/248/763/non_2x/box-package-icon-free-vector.jpg'
            );
        }

        // لو الصورة رابط كامل
        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        // صورة مخزنة محليًا
        return asset('storage/' . ltrim($image, '/'));
    }
}


if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}


if (!function_exists('parsePHPLog')) {
    function parsePHPLog($content)
    {
        $lines = explode("\n", $content);
        $errors = [];
        $currentError = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Check for new error entry (PHP format)
            if (preg_match('/^\[(.*?)\] (PHP )?(.*?): (.*)$/', $line, $matches)) {
                if ($currentError) {
                    $errors[] = $currentError;
                }

                $currentError = [
                    'timestamp' => $matches[1] ?? '',
                    'level' => $matches[3] ?? '',
                    'message' => $matches[4] ?? '',
                    'file' => '',
                    'line' => '',
                    'stack' => ''
                ];
            }
            // Check for Laravel format
            elseif (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) (.*?): (.*)$/', $line, $matches)) {
                if ($currentError) {
                    $errors[] = $currentError;
                }

                $currentError = [
                    'timestamp' => $matches[1] ?? '',
                    'level' => $matches[2] ?? '',
                    'message' => $matches[3] ?? '',
                    'file' => '',
                    'line' => '',
                    'stack' => ''
                ];
            }
            // Stack trace or file info
            elseif ($currentError) {
                if (str_contains($line, 'Stack trace:')) {
                    $currentError['stack'] = $line;
                } elseif (preg_match('/ in (.*?) on line (\d+)/', $line, $matches)) {
                    $currentError['file'] = $matches[1] ?? '';
                    $currentError['line'] = $matches[2] ?? '';
                } elseif (str_starts_with($line, '#') && !empty($currentError['stack'])) {
                    $currentError['stack'] .= "\n" . $line;
                }
            }
        }

        if ($currentError) {
            $errors[] = $currentError;
        }

        return $errors;
    }
}



if (!function_exists('module_icon')) {
    function module_icon(string $module): string
    {
        return \App\Helpers\PermissionHelper::getModuleIcon($module);
    }
}

if (!function_exists('module_display_name')) {
    function module_display_name(string $module): string
    {
        return \App\Helpers\PermissionHelper::getModuleDisplayName($module);
    }
}

if (!function_exists('permission_type')) {
    function permission_type(string $permissionName): string
    {
        return \App\Helpers\PermissionHelper::getPermissionType($permissionName);
    }
}

if (!function_exists('permission_type_label')) {
    function permission_type_label(string $permissionName): string
    {
        return \App\Helpers\PermissionHelper::getPermissionTypeLabel($permissionName);
    }
}

if (!function_exists('permission_badge_class')) {
    function permission_badge_class(string $permissionName): string
    {
        return \App\Helpers\PermissionHelper::getPermissionBadgeClass($permissionName);
    }
}


if (!function_exists('module_icon')) {
    function module_icon(string $module): string
    {
        return \App\Helpers\PermissionHelper::getModuleIcon($module);
    }
}

if (!function_exists('module_display_name')) {
    function module_display_name(string $module): string
    {
        return \App\Helpers\PermissionHelper::getModuleDisplayName($module);
    }
}

if (!function_exists('permission_type')) {
    function permission_type(string $permissionName): string
    {
        return \App\Helpers\PermissionHelper::getPermissionType($permissionName);
    }
}

if (!function_exists('permission_type_label')) {
    function permission_type_label(string $permissionName): string
    {
        return \App\Helpers\PermissionHelper::getPermissionTypeLabel($permissionName);
    }
}

if (!function_exists('permission_badge_class')) {
    function permission_badge_class(string $permissionName): string
    {
        return \App\Helpers\PermissionHelper::getPermissionBadgeClass($permissionName);
    }
}



if (!function_exists('formatBytes')) {
    /**
     * Format bytes to human readable format.
     */
    function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

if (!function_exists('getFileIcon')) {
    /**
     * Get icon class for file type.
     */
    function getFileIcon($extension)
    {
        $icons = [
            'jpg' => 'fas fa-image',
            'jpeg' => 'fas fa-image',
            'png' => 'fas fa-image',
            'gif' => 'fas fa-image',
            'bmp' => 'fas fa-image',
            'svg' => 'fas fa-image',

            'pdf' => 'fas fa-file-pdf',
            'doc' => 'fas fa-file-word',
            'docx' => 'fas fa-file-word',
            'txt' => 'fas fa-file-alt',
            'rtf' => 'fas fa-file-alt',

            'mp4' => 'fas fa-file-video',
            'avi' => 'fas fa-file-video',
            'mov' => 'fas fa-file-video',
            'wmv' => 'fas fa-file-video',

            'mp3' => 'fas fa-file-audio',
            'wav' => 'fas fa-file-audio',
            'ogg' => 'fas fa-file-audio',

            'zip' => 'fas fa-file-archive',
            'rar' => 'fas fa-file-archive',
            '7z' => 'fas fa-file-archive',

            'xls' => 'fas fa-file-excel',
            'xlsx' => 'fas fa-file-excel',

            'ppt' => 'fas fa-file-powerpoint',
            'pptx' => 'fas fa-file-powerpoint',
        ];

        return $icons[$extension] ?? 'fas fa-file';
    }
}

if (!function_exists('getFileType')) {
    /**
     * Get file type category.
     */
    function getFileType($extension)
    {
        $types = [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'],
            'document' => ['pdf', 'doc', 'docx', 'txt', 'rtf', 'odt'],
            'video' => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv'],
            'audio' => ['mp3', 'wav', 'ogg', 'm4a', 'flac'],
            'archive' => ['zip', 'rar', '7z', 'tar', 'gz'],
            'spreadsheet' => ['xls', 'xlsx', 'csv', 'ods'],
            'presentation' => ['ppt', 'pptx', 'odp'],
        ];

        foreach ($types as $type => $extensions) {
            if (in_array(strtolower($extension), $extensions)) {
                return $type;
            }
        }

        return 'other';
    }
}

if (!function_exists('greeting')) {
    function greeting(): string
    {
        $hour = (int) now()->format('H');

        if ($hour >= 5 && $hour < 12) {
            return __('صباح الخير') . ' 🌅';
        } elseif ($hour >= 12 && $hour < 17) {
            return __('مساء الخير') . ' ☀️';
        } elseif ($hour >= 17 && $hour < 21) {
            return __('مساء الخير') . ' 🌆';
        } else {
            return __('تصبح على خير') . ' 🌙';
        }
    }
    if (!function_exists('adminTrans')) {
        function adminTrans($value, array $preferred = ['Ar', 'ar', 'en'])
        {
            if (!is_array($value)) {
                return (string) ($value ?? '');
            }

            foreach ($preferred as $lang) {
                if (!empty($value[$lang])) {
                    return (string) $value[$lang];
                }
            }

            foreach ($value as $translation) {
                if (is_string($translation) && trim($translation) !== '') {
                    return trim($translation);
                }
            }

            return '';
        }
    }
}
