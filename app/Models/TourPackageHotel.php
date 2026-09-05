<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourPackageHotel extends Model
{
    use HasFactory;

    protected $fillable = [
        'accommodation_id',
        'city_id',
        'city_name',
        'hotel_name',
        'star_rating',
        'description',
        'room_type',
        'meal_plan',
        'alternative_note',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'star_rating' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(TourPackageAccommodation::class, 'accommodation_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
