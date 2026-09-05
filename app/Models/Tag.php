<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected $casts = [
        'name' => 'array',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_tags');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $tag) {
            $name = $tag->name;

            if (is_array($name)) {
                $name = $name['en'] ?? $name['ar'] ?? reset($name) ?? '';
            }

            $tag->slug = Str::slug((string) $name);
        });
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->translatedValue('name');
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->display_name;
    }
}
