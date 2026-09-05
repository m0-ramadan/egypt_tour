<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Post extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'category_id',
        'destination_id',
        'slug',
        'title',
        'excerpt',
        'content',
        'featured_image',
        'post_type',
        'author_id',
        'published_at',
        'is_featured',
        'is_active',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'title' => 'array',
        'excerpt' => 'array',
        'content' => 'array',
        'seo_title' => 'array',
        'seo_description' => 'array',
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable', 'translatable_type', 'translatable_id');
    }

    public function seoMeta(): MorphMany
    {
        return $this->morphMany(SeoMeta::class, 'model', 'model_type', 'model_id');
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->translatedValue('title');
    }
}
