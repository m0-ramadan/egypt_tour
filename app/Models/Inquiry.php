<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Inquiry extends Model
{
    protected $fillable = [
        'client_id',
        'package_id',
        'destination_id',
        'inquiry_type',
        'full_name',
        'email',
        'phone',
        'country_name',
        'travel_date',
        'budget',
        'adults',
        'children',
        'infants',
        'source',
        'message',
        'status',
        'assigned_to',
        'notes',

        // Legacy compatibility fields still referenced in some admin forms.
        'form_id',
        'first_name',
        'last_name',
        'country',
        'subject',
        'budget_min',
        'budget_max',
        'currency_code',
    ];

    protected $casts = [
        'travel_date' => 'date',
        'budget' => 'decimal:2',
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
    ];

    protected $appends = [
        'name',
        'travellers_count',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_to');
    }

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class);
    }

    public function tailorMadeRequest(): HasOne
    {
        return $this->hasOne(TailorMadeRequest::class);
    }

    public function communications(): HasMany
    {
        return $this->hasMany(Communication::class);
    }

    protected function name(): Attribute
    {
        return Attribute::get(function () {
            if (!empty($this->full_name)) {
                return $this->full_name;
            }

            $legacyName = trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));

            return $legacyName !== '' ? $legacyName : null;
        });
    }

    protected function travellersCount(): Attribute
    {
        return Attribute::get(
            fn () => (int) ($this->adults ?? 0) + (int) ($this->children ?? 0) + (int) ($this->infants ?? 0)
        );
    }

    protected function subject(): Attribute
    {
        return Attribute::get(function ($value) {
            if (!empty($value)) {
                return $value;
            }

            return Str::headline(str_replace('_', ' ', (string) ($this->inquiry_type ?: 'inquiry')));
        });
    }
}
