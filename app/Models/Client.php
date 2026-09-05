<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class Client extends Model
{
    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'phone',
        'country_id',
        'birth_date',
        'passport_number',
        'passport_expiry',
        'nationality',
        'newsletter_subscribed',
        'total_bookings',
        'total_spent',
        'last_activity',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'passport_expiry' => 'date',
        'newsletter_subscribed' => 'boolean',
        'total_spent' => 'decimal:2',
        'last_activity' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Client $client): void {
            if ($client->bookings()->exists()) {
                throw new LogicException('A client with booking history cannot be deleted.');
            }
        });
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function communications(): HasMany
    {
        return $this->hasMany(Communication::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    public function getDateOfBirthAttribute()
    {
        return $this->birth_date;
    }

    public function setDateOfBirthAttribute($value): void
    {
        $this->attributes['birth_date'] = $value;
    }
}
