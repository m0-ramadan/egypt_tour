<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'code',
        'symbol',
        'name',
        'rate_to_default',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'rate_to_default' => 'decimal:8',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function packagePrices(): HasMany
    {
        return $this->hasMany(PackagePrice::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->translatedValue('name');
    }

    public static function convert(float $amount, string $fromCode, string $toCode): float
    {
        $fromCode = strtoupper(trim($fromCode));
        $toCode = strtoupper(trim($toCode));

        if ($fromCode === $toCode || $amount <= 0) {
            return $amount;
        }

        $from = self::where('code', $fromCode)->first();
        $to = self::where('code', $toCode)->first();

        $fromRate = (float) ($from?->exchange_rate ?? $from?->rate_to_default ?? 1.0);
        $toRate = (float) ($to?->exchange_rate ?? $to?->rate_to_default ?? 1.0);

        if ($fromRate <= 0) {
            $fromRate = 1.0;
        }

        return round(($amount / $fromRate) * $toRate, 2);
    }
}
