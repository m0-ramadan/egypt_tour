<?php

namespace App\Models;

use App\Support\LocaleNormalizer;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = ['code', 'name', 'native_name', 'is_default', 'is_active', 'sort_order'];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getDisplayNameAttribute(): string
    {
        $labels = $this->languageLabels();
        $locale = strtolower((string) app()->getLocale());

        if ($locale === 'ar') {
            return $labels['ar'] ?? $this->name;
        }

        return $labels['native'] ?? $labels['en'] ?? ($this->native_name ?: $this->name);
    }

    public function getDisplayFlagCodeAttribute(): string
    {
        return match ($this->normalizedCode()) {
            'en' => 'us',
            'ar' => 'sa',
            'ch', 'zh' => 'cn',
            'ge', 'de' => 'de',
            default => $this->normalizedCode(),
        };
    }

    public function getNormalizedCodeAttribute(): string
    {
        return $this->normalizedCode();
    }

    protected function normalizedCode(): string
    {
        return app(LocaleNormalizer::class)->normalize((string) $this->code);
    }

    protected function languageLabels(): array
    {
        return match ($this->normalizedCode()) {
            'en' => ['ar' => 'الإنجليزية', 'en' => 'English', 'native' => 'English'],
            'it' => ['ar' => 'الإيطالية', 'en' => 'Italian', 'native' => 'Italiano'],
            'fr' => ['ar' => 'الفرنسية', 'en' => 'French', 'native' => 'Français'],
            'es' => ['ar' => 'الإسبانية', 'en' => 'Spanish', 'native' => 'Español'],
            'ru' => ['ar' => 'الروسية', 'en' => 'Russian', 'native' => 'Русский'],
            'ch', 'zh' => ['ar' => 'الصينية', 'en' => 'Chinese', 'native' => '中文'],
            'ge', 'de' => ['ar' => 'الألمانية', 'en' => 'German', 'native' => 'Deutsch'],
            'ar' => ['ar' => 'العربية', 'en' => 'Arabic', 'native' => 'العربية'],
            default => [
                'ar' => (string) $this->name,
                'en' => (string) ($this->native_name ?: $this->name),
                'native' => (string) ($this->native_name ?: $this->name),
            ],
        };
    }
}
