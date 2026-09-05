<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Menu extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'name',
        'location_key',
    ];

    protected $casts = [
        'name' => 'array',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('sort_order');
    }

    public function allItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable', 'translatable_type', 'translatable_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->translatedValue('name');
    }
}
