<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'label',
        'url',
        'target',
        'linked_type',
        'linked_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'label' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function getDisplayLabelAttribute(): string
    {
        return $this->translatedValue('label');
    }
}
