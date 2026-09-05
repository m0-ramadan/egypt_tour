<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingTraveler extends Model
{
    protected $fillable = [
        'booking_id',
        'traveler_type',
        'title',
        'first_name',
        'last_name',
        'sort_order',
    ];

    protected $casts = ['sort_order' => 'integer'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
