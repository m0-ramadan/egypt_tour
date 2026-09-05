<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackagePrice extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'package_id',
        'label',
        'season_name',
        'price_type',
        'room_type',
        'pax_min',
        'pax_max',
        'group_size_min',
        'group_size_max',
        'amount',
        'currency_id',
        'valid_from',
        'valid_to',
        'notes',
    ];

    protected $casts = [
        'label' => 'array',
        'season_name' => 'array',
        'notes' => 'array',
        'amount' => 'decimal:2',
        'pax_min' => 'integer',
        'pax_max' => 'integer',
        'group_size_min' => 'integer',
        'group_size_max' => 'integer',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function getDisplayLabelAttribute(): string
    {
        return $this->translatedValue('label');
    }
}
