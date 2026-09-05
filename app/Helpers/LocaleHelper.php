<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;

class LocaleHelper
{
    public static function loadLocale($locale = 'ar')
    {
        $filePath = public_path("assets/json/locales/{$locale}.json");
        
        if (File::exists($filePath)) {
            $content = File::get($filePath);
            return json_decode($content, true);
        }
        
        // Fallback to Arabic if locale not found
        $fallbackPath = public_path("assets/json/locales/ar.json");
        if (File::exists($fallbackPath)) {
            $content = File::get($fallbackPath);
            return json_decode($content, true);
        }
        
        return [];
    }
    
    public static function trans($key, $locale = 'ar', $replacements = [])
    {
        $locales = self::loadLocale($locale);
        
        // Navigate through nested keys (e.g., "app.name")
        $keys = explode('.', $key);
        $value = $locales;
        
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $key; // Return key if not found
            }
        }
        
        // Replace placeholders if any
        if (!empty($replacements)) {
            foreach ($replacements as $placeholder => $replacement) {
                $value = str_replace(':' . $placeholder, $replacement, $value);
            }
        }
        
        return $value;
    }
}