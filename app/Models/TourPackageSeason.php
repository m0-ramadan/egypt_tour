<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TourPackageSeason extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'accommodation_id',
        'name',
        'date_from',
        'date_to',
        'currency_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'date_from' => 'date',
        'date_to' => 'date',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(TourPackageAccommodation::class, 'accommodation_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TourPackagePriceItem::class, 'season_id')->orderBy('sort_order');
    }

    public function getDisplaySeasonNameAttribute(): ?string
    {
        $val = $this->name;
        if (is_array($val)) {
            $locale = app()->getLocale();
            return $val[$locale] ?? $val['en'] ?? reset($val) ?: null;
        }
        return $val ? (string) $val : null;
    }
}
