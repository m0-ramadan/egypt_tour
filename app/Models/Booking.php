<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class Booking extends Model
{
    protected $fillable = [
        'client_id',
        'inquiry_id',
        'package_id',
        'booking_number',
        'status',
        'total_amount',
        'paid_amount',
        'currency_code',
        'payment_status',
        'booking_date',
        'travel_date',
        'adults',
        'children',
        'infants',
        'pickup_location',
        'special_requests',
        'checkout_details',
        'confirmed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'booking_date' => 'date',
        'travel_date' => 'date',
        'adults' => 'integer',
        'children' => 'integer',
        'infants' => 'integer',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'checkout_details' => 'array',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Booking $booking): void {
            if ($booking->payments()->exists()) {
                throw new LogicException('A booking with payment history cannot be deleted.');
            }
        });
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function travelers(): HasMany
    {
        return $this->hasMany(BookingTraveler::class)->orderBy('sort_order');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function communications(): HasMany
    {
        return $this->hasMany(Communication::class);
    }

    public function getRemainingAmountAttribute(): string
    {
        $factor = (int) config('services.paymob.minor_unit_factor', 100);
        $total = Money::toMinor((string) ($this->total_amount ?? 0), $factor);
        $paid = Money::toMinor((string) ($this->paid_amount ?? 0), $factor);

        return Money::fromMinor(max(0, $total - $paid), $factor);
    }

    public function getBookingReferenceAttribute(): ?string
    {
        return $this->booking_number;
    }

    public function setBookingReferenceAttribute(?string $value): void
    {
        $this->attributes['booking_number'] = $value;
    }

    public function getTravellersCountAttribute(): int
    {
        return (int) ($this->adults ?? 0)
            + (int) ($this->children ?? 0)
            + (int) ($this->infants ?? 0);
    }

    public function getPhoneAttribute(): ?string
    {
        return $this->relationLoaded('client')
            ? $this->client?->phone
            : $this->client()->value('phone');
    }

    public function getEmailAttribute(): ?string
    {
        return $this->relationLoaded('client')
            ? $this->client?->email
            : $this->client()->value('email');
    }

    public function getNotesAttribute(): ?string
    {
        return $this->special_requests;
    }

    public function setNotesAttribute(?string $value): void
    {
        $this->attributes['special_requests'] = $value;
    }

    public function getClientNameAttribute(): string
    {
        $client = $this->relationLoaded('client') ? $this->client : $this->client()->first();

        return $client?->full_name ?? '';
    }
}
