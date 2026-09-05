<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'recipient_email',
        'success_message',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'success_message' => 'array',
        'is_active' => 'boolean',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->translatedValue('name');
    }
}
