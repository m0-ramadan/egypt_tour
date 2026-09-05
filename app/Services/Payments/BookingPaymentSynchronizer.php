<?php

namespace App\Services\Payments;

use App\Models\Booking;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\PaymentRefund;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class BookingPaymentSynchronizer
{
    public function sync(Booking|int $booking): Booking
    {
        $bookingId = $booking instanceof Booking ? $booking->getKey() : $booking;
        $factor = (int) config('services.paymob.minor_unit_factor', 100);

        return DB::transaction(function () use ($bookingId, $factor): Booking {
            /** @var Booking $locked */
            $locked = Booking::query()
                ->with('client')
                ->lockForUpdate()
                ->findOrFail($bookingId);

            $payments = Payment::query()
                ->where('booking_id', $locked->id)
                ->with(['refunds' => fn($query) => $query->where('status', PaymentRefund::STATUS_SUCCEEDED)])
                ->lockForUpdate()
                ->get();

            $grossMinor = 0;
            $refundedMinor = 0;
            $bookingCurrency = strtoupper((string) ($locked->currency_code ?: 'USD'));

            foreach ($payments as $payment) {
                $paymentCurrency = strtoupper((string) ($payment->currency_code ?: $bookingCurrency));
                $paymentAmount = (float) $payment->amount;
                if ($paymentCurrency !== $bookingCurrency) {
                    $paymentAmount = Currency::convert($paymentAmount, $paymentCurrency, $bookingCurrency);
                }

                if (in_array($payment->status, [
                    Payment::STATUS_PAID,
                    Payment::STATUS_PARTIALLY_PAID,
                    Payment::STATUS_PARTIALLY_REFUNDED,
                    Payment::STATUS_REFUNDED,
                ], true)) {
                    $grossMinor += Money::toMinor((string) $payment->amount, $factor);
                    $grossMinor += Money::toMinor((string) $paymentAmount, $factor);
                }

                $refundCacheMinor = Money::toMinor((string) ($payment->refunded_amount ?? 0), $factor);
                $refundCache = (float) ($payment->refunded_amount ?? 0);
                if ($paymentCurrency !== $bookingCurrency && $refundCache > 0) {
                    $refundCache = Currency::convert($refundCache, $paymentCurrency, $bookingCurrency);
                }
                $refundCacheMinor = Money::toMinor((string) $refundCache, $factor);
                $refundRowsMinor = 0;

                foreach ($payment->refunds as $refund) {
                    $refundRowsMinor += Money::toMinor((string) $refund->amount, $factor);
                    $refundCurrency = strtoupper((string) ($refund->currency_code ?: $paymentCurrency));
                    $refundAmount = (float) $refund->amount;
                    if ($refundCurrency !== $bookingCurrency) {
                        $refundAmount = Currency::convert($refundAmount, $refundCurrency, $bookingCurrency);
                    }
                    $refundRowsMinor += Money::toMinor((string) $refundAmount, $factor);
                }

                $refundedMinor += max($refundCacheMinor, $refundRowsMinor);
            }

            $netMinor = max(0, $grossMinor - $refundedMinor);
            $totalMinor = Money::toMinor((string) $locked->total_amount, $factor);

            if ($netMinor <= 0) {
                $paymentStatus = $grossMinor > 0 && $refundedMinor >= $grossMinor
                    ? 'refunded'
                    : 'unpaid';
            } elseif ($netMinor >= $totalMinor) {
                $paymentStatus = 'paid';
            } elseif ($refundedMinor > 0) {
                // Existing booking schema has no partially_refunded aggregate value.
                // "partial" safely represents the remaining paid balance.
                $paymentStatus = 'partial';
            } else {
                $paymentStatus = 'partial';
            }

            $changes = [
                'paid_amount' => Money::fromMinor($netMinor, $factor),
                'payment_status' => $paymentStatus,
            ];

            if (
                $paymentStatus === 'paid'
                && ! in_array($locked->status, ['cancelled', 'completed'], true)
            ) {
                $changes['status'] = 'paid';
            } elseif ($locked->status === 'paid' && $paymentStatus !== 'paid') {
                $changes['status'] = 'confirmed';
            }

            $locked->forceFill($changes)->save();

            if ($locked->client_id) {
                $client = $locked->client()->lockForUpdate()->first();

                if ($client) {
                    $client->forceFill([
                        'total_bookings' => Booking::where('client_id', $client->id)->count(),
                        'total_spent' => Booking::where('client_id', $client->id)->sum('paid_amount'),
                        'last_activity' => now(),
                    ])->save();
                }
            }

            return $locked->fresh(['client', 'payments']);
        }, 3);
    }
}
