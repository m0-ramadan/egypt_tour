<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageAttraction extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'package_id',
        'attraction_id',
        'title',
        'teaser',
        'image',
        'sort_order',
    ];

    protected $casts = [
        'title' => 'array',
        'teaser' => 'array',
        'sort_order' => 'integer',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function attraction(): BelongsTo
    {
        return $this->belongsTo(Attraction::class);
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->translatedValue('title') ?: ($this->attraction?->display_name ?? '');
    }
}
