<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageHighlight extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'package_id',
        'title',
        'description',
        'icon',
        'sort_order',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'sort_order' => 'integer',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->translatedValue('title');
    }
}
