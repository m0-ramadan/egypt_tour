<?php

namespace Tests\Feature;

use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\CreatesPaymobPaymentFixtures;
use Tests\TestCase;

class PaymobPaymentFlowTest extends TestCase
{
    use RefreshDatabase;
    use CreatesPaymobPaymentFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configurePaymob();
    }

    public function test_successful_payment_updates_payment_and_booking_once(): void
    {
        $booking = $this->makeBooking('100.00');
        $payment = $this->makePendingPayment($booking, '100.00');
        $obj = $this->transactionObject($payment);

        $response = $this->postJson(
            '/api/v1/paymob/webhook?hmac=' . $this->hmacFor($obj),
            ['type' => 'TRANSACTION', 'obj' => $obj]
        );

        $response->assertOk();

        $payment->refresh();
        $booking->refresh();

        $this->assertSame(Payment::STATUS_PAID, $payment->status);
        $this->assertSame('100.00', (string) $booking->paid_amount);
        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame('paid', $booking->status);
    }

    public function test_failed_payment_does_not_increase_paid_amount(): void
    {
        $booking = $this->makeBooking('100.00');
        $payment = $this->makePendingPayment($booking, '100.00');
        $obj = $this->transactionObject($payment, [
            'success' => false,
            'data' => ['message' => 'Declined'],
        ]);

        $this->postJson(
            '/api/v1/paymob/webhook?hmac=' . $this->hmacFor($obj),
            ['type' => 'TRANSACTION', 'obj' => $obj]
        )->assertOk();

        $payment->refresh();
        $booking->refresh();

        $this->assertSame(Payment::STATUS_FAILED, $payment->status);
        $this->assertSame('0.00', (string) $booking->paid_amount);
        $this->assertSame('unpaid', $booking->payment_status);
    }

    public function test_duplicate_webhook_is_idempotent(): void
    {
        $booking = $this->makeBooking('100.00');
        $payment = $this->makePendingPayment($booking, '100.00');
        $obj = $this->transactionObject($payment);

        $url = '/api/v1/paymob/webhook?hmac=' . $this->hmacFor($obj);

        $this->postJson($url, ['type' => 'TRANSACTION', 'obj' => $obj])->assertOk();
        $this->postJson($url, ['type' => 'TRANSACTION', 'obj' => $obj])->assertOk();

        $booking->refresh();

        $this->assertSame('100.00', (string) $booking->paid_amount);
        $this->assertSame(1, Payment::where('booking_id', $booking->id)->count());
    }

    public function test_invalid_hmac_is_rejected_without_state_change(): void
    {
        $booking = $this->makeBooking('100.00');
        $payment = $this->makePendingPayment($booking, '100.00');
        $obj = $this->transactionObject($payment);

        $this->postJson(
            '/api/v1/paymob/webhook?hmac=' . str_repeat('0', 128),
            ['type' => 'TRANSACTION', 'obj' => $obj]
        )->assertStatus(401);

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertSame('0.00', (string) $booking->fresh()->paid_amount);
    }

    public function test_amount_mismatch_is_rejected(): void
    {
        $booking = $this->makeBooking('100.00');
        $payment = $this->makePendingPayment($booking, '100.00');
        $obj = $this->transactionObject($payment, ['amount_cents' => 9999]);

        $this->postJson(
            '/api/v1/paymob/webhook?hmac=' . $this->hmacFor($obj),
            ['type' => 'TRANSACTION', 'obj' => $obj]
        )->assertStatus(422);

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertSame('0.00', (string) $booking->fresh()->paid_amount);
    }

    public function test_currency_mismatch_is_rejected(): void
    {
        $booking = $this->makeBooking('100.00');
        $payment = $this->makePendingPayment($booking, '100.00');
        $obj = $this->transactionObject($payment, ['currency' => 'USD']);

        $this->postJson(
            '/api/v1/paymob/webhook?hmac=' . $this->hmacFor($obj),
            ['type' => 'TRANSACTION', 'obj' => $obj]
        )->assertStatus(422);

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertSame('0.00', (string) $booking->fresh()->paid_amount);
    }


    public function test_integration_id_mismatch_is_rejected(): void
    {
        $booking = $this->makeBooking('100.00');
        $payment = $this->makePendingPayment($booking, '100.00');
        $obj = $this->transactionObject($payment, ['integration_id' => 999999]);

        $this->postJson(
            '/api/v1/paymob/webhook?hmac=' . $this->hmacFor($obj),
            ['type' => 'TRANSACTION', 'obj' => $obj]
        )->assertStatus(422);

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertSame('0.00', (string) $booking->fresh()->paid_amount);
    }

    public function test_stale_failed_callback_cannot_regress_paid_payment(): void
    {
        $booking = $this->makeBooking('100.00');
        $payment = $this->makePendingPayment($booking, '100.00');
        $success = $this->transactionObject($payment);
        $url = '/api/v1/paymob/webhook?hmac=' . $this->hmacFor($success);

        $this->postJson($url, ['type' => 'TRANSACTION', 'obj' => $success])->assertOk();

        $failed = $this->transactionObject($payment, [
            'success' => false,
            'pending' => false,
            'data' => ['message' => 'Late failure callback'],
        ]);

        $this->postJson(
            '/api/v1/paymob/webhook?hmac=' . $this->hmacFor($failed),
            ['type' => 'TRANSACTION', 'obj' => $failed]
        )->assertOk();

        $this->assertSame(Payment::STATUS_PAID, $payment->fresh()->status);
        $this->assertSame('100.00', (string) $booking->fresh()->paid_amount);
    }

    public function test_second_concurrent_full_checkout_is_blocked_while_first_is_pending(): void
    {
        $booking = $this->makeBooking('100.00');
        $this->paymobMethod();

        Http::fake([
            'https://accept.paymob.com/v1/intention/' => Http::response([
                'id' => 'pi_pending_1',
                'intention_order_id' => 555,
                'client_secret' => 'client-secret-pending',
            ], 201),
        ]);

        $first = $this->postJson(
            '/api/v1/bookings/' . $booking->booking_number . '/payments/paymob',
            ['email' => $booking->client->email, 'payment_type' => 'full']
        );
        $first->assertCreated();

        $second = $this->postJson(
            '/api/v1/bookings/' . $booking->booking_number . '/payments/paymob',
            ['email' => $booking->client->email, 'payment_type' => 'full']
        );

        $second->assertStatus(422);
        $this->assertSame(1, Payment::where('booking_id', $booking->id)->count());
    }

    public function test_required_percentage_deposit_creates_partial_payment_checkout(): void
    {
        $booking = $this->makeBooking('1000.00', [
            'deposit_policy' => 'required',
            'deposit_type' => 'percent',
            'deposit_value' => '25.00',
        ]);

        $this->paymobMethod();

        Http::fake([
            'https://accept.paymob.com/v1/intention/' => Http::response([
                'id' => 'pi_test_1',
                'intention_order_id' => 321,
                'client_secret' => 'client-secret-test',
            ], 201),
        ]);

        $response = $this->postJson(
            '/api/v1/bookings/' . $booking->booking_number . '/payments/paymob',
            [
                'email' => $booking->client->email,
                'payment_type' => 'deposit',
            ]
        );

        $response->assertCreated()
            ->assertJsonPath('status', Payment::STATUS_PENDING)
            ->assertJsonPath('amount', '250.00');

        $payment = Payment::where('transaction_reference', $response->json('payment_reference'))->firstOrFail();

        $this->assertSame('deposit', $payment->payment_type);
        $this->assertSame('250.00', (string) $payment->amount);
        $this->assertStringContainsString('unifiedcheckout', (string) $payment->checkout_url);

        $obj = $this->transactionObject($payment, [
            'amount_cents' => 25000,
            'id' => 900002,
            'order' => [
                'id' => 800002,
                'merchant_order_id' => $payment->transaction_reference,
            ],
        ]);

        $this->postJson(
            '/api/v1/paymob/webhook?hmac=' . $this->hmacFor($obj),
            ['type' => 'TRANSACTION', 'obj' => $obj]
        )->assertOk();

        $booking->refresh();

        $this->assertSame('250.00', (string) $booking->paid_amount);
        $this->assertSame('partial', $booking->payment_status);
    }
}
