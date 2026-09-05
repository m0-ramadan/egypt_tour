<?php

namespace Tests\Concerns;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Package;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Support\Money;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

trait CreatesPaymobPaymentFixtures
{
    protected function configurePaymob(): void
    {
        Config::set('services.paymob.enabled', true);
        Config::set('services.paymob.base_url', 'https://accept.paymob.com');
        Config::set('services.paymob.checkout_url', 'https://accept.paymob.com/unifiedcheckout/');
        Config::set('services.paymob.secret_key', 'test-secret-key');
        Config::set('services.paymob.public_key', 'test-public-key');
        Config::set('services.paymob.api_key', 'test-api-key');
        Config::set('services.paymob.hmac_secret', 'test-hmac-secret');
        Config::set('services.paymob.integration_ids', [123456]);
        Config::set('services.paymob.currency', 'EGP');
        Config::set('services.paymob.minor_unit_factor', 100);
        Config::set('services.paymob.timeout', 5);
        Config::set('services.paymob.pending_hold_minutes', 30);
        Config::set('services.paymob.notification_url', null);
        Config::set('services.paymob.redirection_url', null);
    }

    protected function paymobMethod(): PaymentMethod
    {
        return PaymentMethod::query()->updateOrCreate(
            ['code' => 'paymob'],
            [
                'name' => 'Paymob',
                'provider' => 'paymob',
                'currency_code' => 'EGP',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 100,
            ]
        );
    }

    protected function makeBooking(
        string $total = '100.00',
        array $packageOverrides = [],
    ): Booking {
        $client = Client::create([
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'email' => Str::uuid() . '@example.test',
            'phone' => '+201000000000',
            'is_active' => true,
        ]);

        $package = Package::create(array_merge([
            'slug' => 'test-package-' . Str::uuid(),
            'title' => ['en' => 'Test Package', 'ar' => 'اختبار'],
            'package_type' => 'travel_package',
            'deposit_policy' => null,
            'deposit_type' => null,
            'deposit_value' => null,
        ], $packageOverrides));

        return Booking::create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'booking_number' => 'BK-' . strtoupper(Str::random(12)),
            'status' => 'pending',
            'total_amount' => $total,
            'paid_amount' => '0.00',
            'currency_code' => 'EGP',
            'payment_status' => 'unpaid',
            'booking_date' => now()->toDateString(),
            'travel_date' => now()->addMonth()->toDateString(),
            'adults' => 1,
            'children' => 0,
            'infants' => 0,
        ])->load(['client', 'package']);
    }

    protected function makePendingPayment(Booking $booking, string $amount = '100.00'): Payment
    {
        return Payment::create([
            'booking_id' => $booking->id,
            'payment_method_id' => $this->paymobMethod()->id,
            'transaction_reference' => 'PAY-' . strtoupper((string) Str::uuid()),
            'amount' => $amount,
            'currency_code' => 'EGP',
            'status' => Payment::STATUS_PENDING,
            'payment_type' => 'full',
            'refunded_amount' => '0.00',
        ]);
    }

    protected function transactionObject(
        Payment $payment,
        array $overrides = [],
    ): array {
        $defaults = [
            'amount_cents' => Money::toMinor((string) $payment->amount, 100),
            'created_at' => '2026-09-04T10:00:00.000000',
            'currency' => $payment->currency_code,
            'error_occured' => false,
            'has_parent_transaction' => false,
            'id' => 900001,
            'integration_id' => 123456,
            'is_3d_secure' => true,
            'is_auth' => false,
            'is_capture' => false,
            'is_refunded' => false,
            'is_standalone_payment' => true,
            'is_voided' => false,
            'order' => [
                'id' => 800001,
                'merchant_order_id' => $payment->transaction_reference,
            ],
            'owner' => 700001,
            'pending' => false,
            'source_data' => [
                'pan' => '2346',
                'sub_type' => 'MasterCard',
                'type' => 'card',
            ],
            'success' => true,
            'paid_at' => '2026-09-04T10:00:03.000000',
            'refunded_amount_cents' => 0,
            'data' => ['message' => 'Approved'],
        ];

        return array_replace_recursive($defaults, $overrides);
    }

    protected function hmacFor(array $obj): string
    {
        $values = [
            $obj['amount_cents'] ?? '',
            $obj['created_at'] ?? '',
            $obj['currency'] ?? '',
            $obj['error_occured'] ?? '',
            $obj['has_parent_transaction'] ?? '',
            $obj['id'] ?? '',
            $obj['integration_id'] ?? '',
            $obj['is_3d_secure'] ?? '',
            $obj['is_auth'] ?? '',
            $obj['is_capture'] ?? '',
            $obj['is_refunded'] ?? '',
            $obj['is_standalone_payment'] ?? '',
            $obj['is_voided'] ?? '',
            is_array($obj['order'] ?? null) ? ($obj['order']['id'] ?? '') : ($obj['order'] ?? ''),
            $obj['owner'] ?? '',
            $obj['pending'] ?? '',
            $obj['source_data']['pan'] ?? '',
            $obj['source_data']['sub_type'] ?? '',
            $obj['source_data']['type'] ?? '',
            $obj['success'] ?? '',
        ];

        $canonical = implode('', array_map(function ($value): string {
            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }

            return $value === null ? '' : (string) $value;
        }, $values));

        return hash_hmac('sha512', $canonical, 'test-hmac-secret');
    }
}
