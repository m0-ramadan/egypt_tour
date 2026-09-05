<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourPackagePriceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id',
        'occupancy_type',
        'label',
        'price',
        'price_unit',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'label' => 'array',
        'price' => 'float',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(TourPackageSeason::class, 'season_id');
    }

    public function getDisplayLabelAttribute(): ?string
    {
        $val = $this->label;
        if (is_array($val)) {
            $locale = app()->getLocale();
            return $val[$locale] ?? $val['en'] ?? reset($val) ?: null;
        }
        return $val ? (string) $val : null;
    }
}
