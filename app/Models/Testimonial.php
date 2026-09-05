<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'package_id',
        'customer_name',
        'customer_country',
        'initials',
        'source',
        'source_url',
        'rating',
        'content',
        'is_verified',
        'verified_purchase',
        'is_featured',
        'response_from_admin',
        'responded_at',
        'published_at',
        'sort_order',
    ];

    protected $casts = [
        'customer_country' => 'array',
        'content' => 'array',
        'response_from_admin' => 'array',
        'rating' => 'decimal:1',
        'is_verified' => 'boolean',
        'verified_purchase' => 'boolean',
        'is_featured' => 'boolean',
        'responded_at' => 'datetime',
        'published_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function getDisplayContentAttribute(): string
    {
        return $this->translatedValue('content');
    }
}
