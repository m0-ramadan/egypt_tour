<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsletterSubscriber extends Model
{
    protected $fillable = ['email', 'name', 'country_id', 'preferences', 'is_active', 'verified_at', 'unsubscribed_at'];

    protected $casts = [
        'preferences' => 'array',
        'is_active' => 'boolean',
        'verified_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    public function country(): BelongsTo { return $this->belongsTo(Country::class); }
}
