<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItem extends Model
{
    protected $fillable = [
        'booking_id',
        'pricing_source',
        'source_id',
        'cabin_id',
        'option_label',
        'occupancy_type',
        'unit_price',
        'quantity',
        'room_count',
        'total_amount',
        'meta',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'quantity' => 'integer',
        'room_count' => 'integer',
        'meta' => 'array',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function cabin(): BelongsTo
    {
        return $this->belongsTo(NileCruiseCabin::class, 'cabin_id');
    }
}
