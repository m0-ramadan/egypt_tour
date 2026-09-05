<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealCampaign extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'package_id',
        'title',
        'slug',
        'summary',
        'discount_type',
        'discount_value',
        'starts_at',
        'ends_at',
        'is_active',
        'banner_image',
    ];

    protected $casts = [
        'title' => 'array',
        'summary' => 'array',
        'discount_value' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->translatedValue('title');
    }
}
