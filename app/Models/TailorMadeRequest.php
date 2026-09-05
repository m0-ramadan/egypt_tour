<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TailorMadeRequest extends Model
{
    protected $fillable = [
        'inquiry_id',
        'full_name',
        'email',
        'phone',
        'country_of_residence',
        'start_date',
        'end_date',
        'trip_duration',
        'accommodation_preference',
        'adults',
        'children',
        'infants',
        'budget_min',
        'budget_max',
        'occasion',
        'interests',
        'dietary_requirements',
        'mobility_requirements',
        'special_requests',
        'source',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget_min' => 'decimal:2',
        'interests' => 'array',
    ];

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }
}
