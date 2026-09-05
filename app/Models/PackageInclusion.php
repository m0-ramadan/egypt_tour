<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageInclusion extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'package_id',
        'type',
        'item_type',
        'title',
        'content',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'content' => 'array',
        'sort_order' => 'integer',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function getDisplayContentAttribute(): string
    {
        return $this->translatedValue('content')
            ?: $this->translatedValue('title')
            ?: $this->translatedValue('description');
    }
}
