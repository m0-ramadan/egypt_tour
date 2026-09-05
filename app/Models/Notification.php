<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'admin_id',
        'type',
        'title',
        'message',
        'link',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'title' => 'array',
        'message' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->translatedValue('title');
    }

    public function getDisplayMessageAttribute(): string
    {
        return $this->translatedValue('message');
    }
}
