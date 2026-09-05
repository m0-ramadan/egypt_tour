<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NileCruiseType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'featured_image',
        'banner_image',
        'seo_title',
        'seo_description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'short_description' => 'array',
        'seo_title' => 'array',
        'seo_description' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(NileCruiseCategory::class, 'nile_cruise_type_id')->orderBy('sort_order');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class, 'nile_cruise_type_id');
    }

    public function getDisplayNameAttribute(): string
    {
        if (is_array($this->name)) {
            $locale = app()->getLocale();
            return $this->name[$locale] ?? $this->name['en'] ?? reset($this->name) ?: '';
        }

        return (string) $this->name;
    }

    public function getDisplayShortDescriptionAttribute(): string
    {
        if (is_array($this->short_description)) {
            $locale = app()->getLocale();
            return $this->short_description[$locale] ?? $this->short_description['en'] ?? reset($this->short_description) ?: '';
        }

        return (string) $this->short_description;
    }

    public function getDisplayDescriptionAttribute(): string
    {
        if (is_array($this->description)) {
            $locale = app()->getLocale();
            return $this->description[$locale] ?? $this->description['en'] ?? reset($this->description) ?: '';
        }

        return (string) $this->description;
    }

    public function getImageUrlAttribute(): string
    {
        if (!empty($this->featured_image)) {
            if (str_starts_with($this->featured_image, 'http://') || str_starts_with($this->featured_image, 'https://')) {
                return $this->featured_image;
            }
            if (str_starts_with($this->featured_image, 'website/')) {
                return asset($this->featured_image);
            }
            return asset('storage/' . $this->featured_image);
        }

        return match ($this->slug) {
            'dahabiya-nile-cruise' => asset('website/images/nile-cruises/dahabiya.jpg'),
            'lake-nasser-cruise' => asset('website/images/nile-cruises/lake-nasser.jpg'),
            default => asset('website/images/nile-cruises/luxor-aswan.jpg'),
        };
    }

    public function getBannerUrlAttribute(): string
    {
        if (!empty($this->banner_image)) {
            if (str_starts_with($this->banner_image, 'http://') || str_starts_with($this->banner_image, 'https://')) {
                return $this->banner_image;
            }
            if (str_starts_with($this->banner_image, 'website/')) {
                return asset($this->banner_image);
            }
            return asset('storage/' . $this->banner_image);
        }

        return $this->image_url;
    }
}
