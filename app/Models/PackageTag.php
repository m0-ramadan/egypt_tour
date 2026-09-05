<?php

namespace App\Models;

use App\Casts\LegacyTranslationArray;
use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PackageTag extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected $casts = [
        'name' => LegacyTranslationArray::class,
    ];

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_tag_items', 'tag_id', 'package_id')->withTimestamps();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->translatedValue('name');
    }
}
