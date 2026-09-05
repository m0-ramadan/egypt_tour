<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArticleCategory extends Model
{
    use HasFactory, HasTranslatableAttributes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'meta_title',
        'meta_description',
        'parent_id',
        'order',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'meta_title' => 'array',
        'meta_description' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'category_id');
    }

    public function activeArticles(): HasMany
    {
        return $this->hasMany(Article::class, 'category_id')->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMainCategories($query)
    {
        return $query->whereNull('parent_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->translatedValue('name');
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->display_name;
    }

    public function getDisplayDescriptionAttribute(): string
    {
        return $this->translatedValue('description');
    }

    public function getDisplayMetaTitleAttribute(): string
    {
        return $this->translatedValue('meta_title') ?: $this->display_name;
    }

    public function getDisplayMetaDescriptionAttribute(): string
    {
        return $this->translatedValue('meta_description') ?: $this->display_description;
    }
}
