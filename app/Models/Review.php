<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'client_id',
        'package_id',
        'rating',
        'title',
        'content',
        'pros',
        'cons',
        'travel_date',
        'images',
        'is_approved',
        'helpful_count',
    ];

    protected $casts = [
        'title' => 'array',
        'content' => 'array',
        'pros' => 'array',
        'cons' => 'array',
        'rating' => 'decimal:1',
        'travel_date' => 'date',
        'images' => 'array',
        'is_approved' => 'boolean',
        'helpful_count' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->translatedValue('title');
    }
}
