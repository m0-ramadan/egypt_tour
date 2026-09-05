<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Video extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'title',
        'description',
        'video_type',
        'video_id',
        'url',
        'thumbnail',
        'duration',
        'featured',
        'published_at',
        'sort_order',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'featured' => 'boolean',
        'published_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    public function mediables(): HasMany
    {
        return $this->hasMany(VideoMediable::class);
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->translatedValue('title');
    }
}
