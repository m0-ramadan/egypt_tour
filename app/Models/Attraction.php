<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Attraction extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'city_id',
        'slug',
        'name',
        'short_description',
        'description',
        'image',
        'opening_hours',
        'map_url',
        'latitude',
        'longitude',
        'is_featured',
        'is_active',
        'sort_order',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'name' => 'array',
        'short_description' => 'array',
        'description' => 'array',
        'seo_title' => 'array',
        'seo_description' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function packageAttractions(): HasMany
    {
        return $this->hasMany(PackageAttraction::class);
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function seoMeta(): MorphMany
    {
        return $this->morphMany(SeoMeta::class, 'model');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->translatedValue('name');
    }

    public function getDisplayShortDescriptionAttribute(): string
    {
        return $this->translatedValue('short_description');
    }

    public function getDisplayDescriptionAttribute(): string
    {
        return $this->translatedValue('description');
    }
}
