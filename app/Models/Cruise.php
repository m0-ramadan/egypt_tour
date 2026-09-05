<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cruise extends Model
{
    protected $fillable = [
        'package_id',
        'cruise_class',
        'ship_name',
        'route_from',
        'route_to',
        'sailing_days',
        'star_rating',
        'cabin_count',
        'onboard_features',
    ];

    protected $casts = [
        'star_rating' => 'integer',
        'cabin_count' => 'integer',
        'onboard_features' => 'array',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
