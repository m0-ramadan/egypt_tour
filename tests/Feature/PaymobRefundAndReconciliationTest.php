<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\PaymentRefund;
use App\Services\Payments\BookingPaymentSynchronizer;
use App\Services\Payments\PaymobService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\CreatesPaymobPaymentFixtures;
use Tests\TestCase;

class PaymobRefundAndReconciliationTest extends TestCase
{
    use RefreshDatabase;
    use CreatesPaymobPaymentFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configurePaymob();
    }

    public function test_full_refund_is_recorded_and_booking_is_recalculated(): void
    {
        $booking = $this->makeBooking('100.00');
        $payment = $this->makePendingPayment($booking, '100.00');
        $payment->forceFill([
            'status' => Payment::STATUS_PAID,
            'gateway_transaction_id' => '555001',
            'gateway_reference' => '555001',
            'paid_at' => now(),
        ])->save();

        app(BookingPaymentSynchronizer::class)->sync($booking);

        Http::fake([
            'https://accept.paymob.com/api/acceptance/void_refund/refund' => Http::response([
                'id' => 777001,
                'success' => true,
                'pending' => false,
            ], 200),
        ]);

        $refund = app(PaymobService::class)->refund($payment);

        $this->assertSame(PaymentRefund::STATUS_SUCCEEDED, $refund->status);
        $this->assertSame('777001', (string) $refund->gateway_refund_id);

        $payment->refresh();
        $booking->refresh();

        $this->assertSame(Payment::STATUS_REFUNDED, $payment->status);
        $this->assertSame('100.00', (string) $payment->refunded_amount);
        $this->assertSame('0.00', (string) $booking->paid_amount);
        $this->assertSame('refunded', $booking->payment_status);
    }

    public function test_partial_refund_preserves_remaining_paid_balance(): void
    {
        $booking = $this->makeBooking('100.00');
        $payment = $this->makePendingPayment($booking, '100.00');
        $payment->forceFill([
            'status' => Payment::STATUS_PAID,
            'gateway_transaction_id' => '555002',
            'gateway_reference' => '555002',
            'paid_at' => now(),
        ])->save();

        app(BookingPaymentSynchronizer::class)->sync($booking);

        Http::fake([
            'https://accept.paymob.com/api/acceptance/void_refund/refund' => Http::response([
                'id' => 777002,
                'success' => true,
                'pending' => false,
            ], 200),
        ]);

        app(PaymobService::class)->refund($payment, '40.00');

        $payment->refresh();
        $booking->refresh();

        $this->assertSame(Payment::STATUS_PARTIALLY_REFUNDED, $payment->status);
        $this->assertSame('40.00', (string) $payment->refunded_amount);
        $this->assertSame('60.00', (string) $booking->paid_amount);
        $this->assertSame('partial', $booking->payment_status);
    }


    public function test_over_refund_is_rejected_before_gateway_call(): void
    {
        $booking = $this->makeBooking('100.00');
        $payment = $this->makePendingPayment($booking, '100.00');
        $payment->forceFill([
            'status' => Payment::STATUS_PAID,
            'gateway_transaction_id' => '555003',
            'gateway_reference' => '555003',
            'paid_at' => now(),
        ])->save();

        app(BookingPaymentSynchronizer::class)->sync($booking);
        Http::fake();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Refund amount exceeds');

        app(PaymobService::class)->refund($payment, '100.01');
    }

    public function test_ambiguous_refund_failure_stays_pending_and_reconciliation_settles_it(): void
    {
        $booking = $this->makeBooking('100.00');
        $payment = $this->makePendingPayment($booking, '100.00');
        $payment->forceFill([
            'status' => Payment::STATUS_PAID,
            'gateway_transaction_id' => '555004',
            'gateway_reference' => '555004',
            'paid_at' => now(),
        ])->save();

        app(BookingPaymentSynchronizer::class)->sync($booking);

        Http::fake([
            'https://accept.paymob.com/api/acceptance/void_refund/refund' => Http::response([
                'message' => 'Temporary gateway error',
            ], 500),
        ]);

        try {
            app(PaymobService::class)->refund($payment, '40.00');
            $this->fail('Expected Paymob refund request to throw on 500.');
        } catch (\Throwable) {
            // Expected: outcome remains unknown until reconciliation.
        }

        $refund = PaymentRefund::where('payment_id', $payment->id)->latest()->firstOrFail();
        $this->assertSame(PaymentRefund::STATUS_PENDING, $refund->status);

        $obj = $this->transactionObject($payment, [
            'id' => 900014,
            'is_refunded' => false,
            'refunded_amount_cents' => 4000,
        ]);

        Http::fake([
            'https://accept.paymob.com/api/auth/tokens' => Http::response([
                'token' => 'auth-token-test',
            ], 201),
            'https://accept.paymob.com/api/ecommerce/orders/transaction_inquiry' => Http::response($obj, 200),
        ]);

        app(PaymobService::class)->reconcile($payment);

        $this->assertSame(PaymentRefund::STATUS_SUCCEEDED, $refund->fresh()->status);
        $this->assertSame(Payment::STATUS_PARTIALLY_REFUNDED, $payment->fresh()->status);
        $this->assertSame('60.00', (string) $booking->fresh()->paid_amount);
    }

    public function test_reconciliation_promotes_pending_payment_to_paid(): void
    {
        $booking = $this->makeBooking('100.00');
        $payment = $this->makePendingPayment($booking, '100.00');
        $obj = $this->transactionObject($payment, ['id' => 900010]);

        Http::fake([
            'https://accept.paymob.com/api/auth/tokens' => Http::response([
                'token' => 'auth-token-test',
            ], 201),
            'https://accept.paymob.com/api/ecommerce/orders/transaction_inquiry' => Http::response($obj, 200),
        ]);

        $reconciled = app(PaymobService::class)->reconcile($payment);

        $this->assertSame(Payment::STATUS_PAID, $reconciled->status);
        $this->assertNotNull($reconciled->last_reconciled_at);
        $this->assertSame(1, $reconciled->reconciliation_attempts);
        $this->assertSame('100.00', (string) $booking->fresh()->paid_amount);
    }

    public function test_reconciliation_command_processes_stale_pending_payments(): void
    {
        $booking = $this->makeBooking('100.00');
        $payment = $this->makePendingPayment($booking, '100.00');
        $payment->forceFill(['created_at' => now()->subHour()])->save();

        $obj = $this->transactionObject($payment, ['id' => 900011]);

        Http::fake([
            'https://accept.paymob.com/api/auth/tokens' => Http::response([
                'token' => 'auth-token-test',
            ], 201),
            'https://accept.paymob.com/api/ecommerce/orders/transaction_inquiry' => Http::response($obj, 200),
        ]);

        $this->artisan('payments:reconcile-paymob --minutes=15 --limit=10')
            ->assertExitCode(0);

        $this->assertSame(Payment::STATUS_PAID, $payment->fresh()->status);
    }
}
