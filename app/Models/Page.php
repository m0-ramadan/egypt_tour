<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Page extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'slug',
        'title',
        'template',
        'body',
        'featured_image',
        'is_home',
        'is_active',
        'published_at',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'title' => 'array',
        'body' => 'array',
        'seo_title' => 'array',
        'seo_description' => 'array',
        'is_home' => 'boolean',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function translations(): MorphMany
    {
        return $this->morphMany(
            Translation::class,
            'translatable',
            'translatable_type',
            'translatable_id'
        );
    }

    public function seoMeta(): MorphMany
    {
        return $this->morphMany(
            SeoMeta::class,
            'model',
            'model_type',
            'model_id'
        );
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $builder) {
                $builder->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->translatedValue('title');
    }

    public function getDisplayBodyAttribute(): string
    {
        return $this->translatedValue('body');
    }

    public function getDisplaySeoTitleAttribute(): string
    {
        return $this->translatedValue('seo_title');
    }

    public function getDisplaySeoDescriptionAttribute(): string
    {
        return $this->translatedValue('seo_description');
    }
}
