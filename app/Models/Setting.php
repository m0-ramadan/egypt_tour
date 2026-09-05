<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'is_public',
    ];

    protected $casts = [
        'value' => 'array',
        'is_public' => 'boolean',
    ];

    public function getDisplayValueAttribute(): string
    {
        return $this->translatedValue('value');
    }
}
