<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory, HasTranslatableAttributes;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'category_id',
        'author_id',
        'seo_description',
        'seo_title',
        'seo_keywords',
        'is_active',
        'is_featured',
        'views_count',
        'reading_time',
        'published_at',
        'article_type',
    ];

    protected $casts = [
        'title' => 'array',
        'content' => 'array',
        'excerpt' => 'array',
        'seo_description' => 'array',
        'seo_title' => 'array',
        'seo_keywords' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'views_count' => 'integer',
        'reading_time' => 'integer',
        'published_at' => 'datetime',
    ];

    public function getDisplayTitleAttribute(): string
    {
        return $this->translatedValue('title') ?: '';
    }

    public function getDisplayExcerptAttribute(): string
    {
        return $this->translatedValue('excerpt') ?: '';
    }

    public function getDisplayContentAttribute(): string
    {
        return $this->translatedValue('content') ?: '';
    }

    public function getDisplaySeoTitleAttribute(): string
    {
        return $this->translatedValue('seo_title') ?: $this->display_title;
    }

    public function getDisplaySeoDescriptionAttribute(): string
    {
        return $this->translatedValue('seo_description') ?: $this->display_excerpt;
    }

    public function getDisplaySeoKeywordsAttribute(): string
    {
        return $this->translatedValue('seo_keywords') ?: '';
    }

    public function getDisplayMetaTitleAttribute(): string
    {
        return $this->display_seo_title;
    }

    public function getDisplayMetaDescriptionAttribute(): string
    {
        return $this->display_seo_description;
    }

    public function getDisplayMetaKeywordsAttribute(): string
    {
        return $this->display_seo_keywords;
    }

    public function getImageUrlAttribute(): string
    {
        if (!$this->featured_image) {
            return asset('website/photos/home2.webp');
        }

        if (str_starts_with($this->featured_image, 'http://') || str_starts_with($this->featured_image, 'https://')) {
            return $this->featured_image;
        }

        return asset($this->featured_image);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ArticleComment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function calculateReadingTime(?string $content = null): int
    {
        $content = $content ?? $this->display_content;

        $wordCount = str_word_count(strip_tags($content));
        $readingTime = (int) ceil($wordCount / 200);

        return $readingTime > 0 ? $readingTime : 1;
    }
}
