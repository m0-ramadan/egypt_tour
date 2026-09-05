<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceCalendar extends Model
{
    protected $table = 'price_calendar';

    protected $fillable = ['package_id', 'date', 'price', 'availability', 'is_blocked'];

    protected $casts = [
        'date' => 'date',
        'price' => 'decimal:2',
        'is_blocked' => 'boolean',
    ];

    public function package(): BelongsTo { return $this->belongsTo(Package::class); }
}
