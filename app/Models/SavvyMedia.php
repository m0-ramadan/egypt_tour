<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SavvyMedia extends Model
{
    use HasFactory;

    protected $table = 'savvy_media';

    protected $fillable = [
        'remote_id',
        'uuid',
        'storage_type',
        'filename',
        'original_filename',
        'mime_type',
        'size_bytes',
        'size_human',
        'url',
        'webp_url',
        'thumbnail_url',
        'local_path',
        'local_thumbnail_path',
        'is_downloaded',
        'thumbnails',
        'category',
        'tags',
        'country_slug',
        'city_slug',
        'sub_category',
        'alt_text',
        'title',
        'description',
        'type',
        'is_global',
        'is_public',
        'remote_created_at',
        'last_synced_at',
    ];

    protected $casts = [
        'thumbnails' => 'array',
        'tags' => 'array',
        'is_global' => 'boolean',
        'is_public' => 'boolean',
        'is_downloaded' => 'boolean',
        'remote_created_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    protected $appends = [
        'local_url',
        'display_url',
        'display_thumbnail_url',
    ];

    /**
     * Get the full URL of the locally stored image if available.
     */
    public function getLocalUrlAttribute(): ?string
    {
        if ($this->local_path && Storage::disk('public')->exists($this->local_path)) {
            return asset('storage/' . $this->local_path);
        }
        return null;
    }

    /**
     * Get the full URL of the locally stored thumbnail if available.
     */
    public function getLocalThumbnailUrlAttribute(): ?string
    {
        if ($this->local_thumbnail_path && Storage::disk('public')->exists($this->local_thumbnail_path)) {
            return asset('storage/' . $this->local_thumbnail_path);
        }
        return null;
    }

    /**
     * Priority: Local URL -> Remote WebP URL -> Remote URL
     */
    public function getDisplayUrlAttribute(): ?string
    {
        return $this->local_url ?: ($this->webp_url ?: $this->url);
    }

    /**
     * Priority: Local Thumbnail -> Local URL -> Remote Thumbnail -> Remote WebP -> Remote URL
     */
    public function getDisplayThumbnailUrlAttribute(): ?string
    {
        return $this->local_thumbnail_url ?: ($this->local_url ?: ($this->thumbnail_url ?: ($this->webp_url ?: $this->url)));
    }
}
