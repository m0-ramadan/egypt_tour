<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'country_id',
        'name',
        'slug',
        'short_description',
        'description',
        'hero_image',
        'featured_image',
        'latitude',
        'longitude',
        'is_featured',
        'is_active',
        'sort_order',
        'seo_title',
        'seo_description',
        'schema_json',
    ];

    protected $casts = [
        'name' => 'array',
        'short_description' => 'array',
        'description' => 'array',
        'seo_title' => 'array',
        'seo_description' => 'array',
        'schema_json' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function attractions(): HasMany
    {
        return $this->hasMany(Attraction::class);
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_cities', 'city_id', 'package_id')
            ->withPivot(['stop_order', 'is_primary', 'nights'])
            ->withTimestamps();
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
