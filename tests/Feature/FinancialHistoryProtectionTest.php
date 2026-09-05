<?php

namespace Tests\Feature;

use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesPaymobPaymentFixtures;
use Tests\TestCase;

class FinancialHistoryProtectionTest extends TestCase
{
    use RefreshDatabase;
    use CreatesPaymobPaymentFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configurePaymob();
    }

    public function test_booking_with_payment_history_cannot_be_deleted(): void
    {
        $booking = $this->makeBooking('100.00');
        $this->makePendingPayment($booking, '100.00');

        $this->expectException(\LogicException::class);
        $booking->delete();
    }

    public function test_client_with_booking_history_cannot_be_deleted(): void
    {
        $booking = $this->makeBooking('100.00');
        $client = $booking->client;

        $this->expectException(\LogicException::class);
        $client->delete();
    }

    public function test_gateway_payment_history_cannot_be_deleted(): void
    {
        $booking = $this->makeBooking('100.00');
        $payment = $this->makePendingPayment($booking, '100.00');
        $payment->forceFill([
            'status' => Payment::STATUS_PAID,
            'gateway_transaction_id' => 'history-transaction-1',
        ])->save();

        $this->expectException(\LogicException::class);
        $payment->delete();
    }
}
