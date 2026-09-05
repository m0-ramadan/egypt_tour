<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class Payment extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_PARTIALLY_PAID = 'partially_paid';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    protected $fillable = [
        'booking_id',
        'payment_method_id',
        'transaction_reference',
        'gateway_reference',
        'gateway_intention_id',
        'gateway_transaction_id',
        'gateway_order_id',
        'checkout_url',
        'amount',
        'currency_code',
        'status',
        'payment_type',
        'paid_at',
        'refunded_amount',
        'refunded_at',
        'last_reconciled_at',
        'reconciliation_attempts',
        'failure_reason',
        'gateway_payload',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'last_reconciled_at' => 'datetime',
        'reconciliation_attempts' => 'integer',
        'gateway_payload' => 'array',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Payment $payment): void {
            if ($payment->isPaymob()
                || $payment->gateway_transaction_id
                || $payment->refunds()->exists()) {
                throw new LogicException('Gateway payment history cannot be deleted.');
            }
        });
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(PaymentRefund::class);
    }

    public function isPaymob(): bool
    {
        $method = $this->relationLoaded('paymentMethod')
            ? $this->paymentMethod
            : $this->paymentMethod()->first();

        if ($method) {
            return strtolower((string) ($method->provider ?? '')) === 'paymob'
                || strtolower((string) ($method->code ?? '')) === 'paymob';
        }

        return (bool) ($this->gateway_intention_id || $this->gateway_transaction_id || $this->checkout_url);
    }

    public function getRefundableAmountAttribute(): string
    {
        $factor = (int) config('services.paymob.minor_unit_factor', 100);
        $amount = Money::toMinor((string) ($this->amount ?? 0), $factor);
        $refunded = Money::toMinor((string) ($this->refunded_amount ?? 0), $factor);

        return Money::fromMinor(max(0, $amount - $refunded), $factor);
    }
}
