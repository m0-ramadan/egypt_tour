<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourPackageDetail extends Model
{
    protected $fillable = [
        'package_id', 'accommodation_standard', 'meals_included', 'flexible_itinerary', 'additional_notes',
    ];

    protected $casts = [
        'meals_included' => 'array',
        'flexible_itinerary' => 'boolean',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
