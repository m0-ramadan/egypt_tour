<?php

namespace App\Services\Payments;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Support\Money;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;

class PayPalService
{
    public function __construct(private readonly BookingPaymentSynchronizer $synchronizer) {}

    public function createCheckout(Booking $booking, PaymentMethod $method): Payment
    {
        $this->ensureConfigured();
        $booking = $this->synchronizer->sync($booking->loadMissing(['client', 'package']));
        $factor = max(1, (int) config('services.paymob.minor_unit_factor', 100));
        $remaining = Money::toMinor((string) $booking->remaining_amount, $factor);

        if ($remaining <= 0) {
            throw new RuntimeException('Booking has no outstanding balance.');
        }

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'payment_method_id' => $method->id,
            'transaction_reference' => 'PAYPAL-'.strtoupper((string) Str::uuid()),
            'amount' => Money::fromMinor($remaining, $factor),
            'currency_code' => strtoupper((string) $booking->currency_code),
            'status' => Payment::STATUS_PENDING,
            'payment_type' => 'full',
            'refunded_amount' => '0.00',
        ]);

        try {
            $response = $this->request($this->accessToken())
                ->withHeaders(['PayPal-Request-Id' => $payment->transaction_reference])
                ->post('/v2/checkout/orders', [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [[
                        'reference_id' => $payment->transaction_reference,
                        'custom_id' => $payment->transaction_reference,
                        'description' => 'Travel booking '.$booking->booking_number,
                        'amount' => [
                            'currency_code' => $payment->currency_code,
                            'value' => number_format((float) $payment->amount, 2, '.', ''),
                        ],
                    ]],
                    'payment_source' => [
                        'paypal' => [
                            'experience_context' => [
                                'brand_name' => config('app.name', 'Etro Tours'),
                                'shipping_preference' => 'NO_SHIPPING',
                                'user_action' => 'PAY_NOW',
                                'return_url' => route('website.checkout.paypal.capture', [
                                    'reference' => $payment->transaction_reference,
                                ]),
                                'cancel_url' => URL::temporarySignedRoute('website.checkout.status', now()->addHours(24), [
                                    'paymentReference' => $payment->transaction_reference,
                                    'result' => 'cancelled',
                                ]),
                            ],
                        ],
                    ],
                ]);
            $response->throw();
            $payload = (array) $response->json();
            $approvalUrl = collect($payload['links'] ?? [])->firstWhere('rel', 'payer-action')['href']
                ?? collect($payload['links'] ?? [])->firstWhere('rel', 'approve')['href']
                ?? null;

            if (! $approvalUrl || empty($payload['id'])) {
                throw new RuntimeException('PayPal did not return an approval URL.');
            }

            $payment->forceFill([
                'gateway_order_id' => (string) $payload['id'],
                'gateway_reference' => (string) $payload['id'],
                'checkout_url' => (string) $approvalUrl,
                'gateway_payload' => $this->safePayload($payload),
            ])->save();

            return $payment->fresh();
        } catch (\Throwable $exception) {
            $payment->forceFill([
                'status' => Payment::STATUS_FAILED,
                'failure_reason' => $exception->getMessage(),
            ])->save();
            throw $exception;
        }
    }

    public function capture(Payment $payment, string $orderId): Payment
    {
        $this->ensureConfigured();

        if (! hash_equals((string) $payment->gateway_order_id, $orderId)) {
            throw new RuntimeException('PayPal order verification failed.');
        }

        if ($payment->status === Payment::STATUS_PAID) {
            return $payment;
        }

        $response = $this->request($this->accessToken())
            ->post('/v2/checkout/orders/'.rawurlencode($orderId).'/capture');
        $response->throw();
        $payload = (array) $response->json();
        $capture = data_get($payload, 'purchase_units.0.payments.captures.0', []);
        $receivedAmount = (string) data_get($capture, 'amount.value', '');
        $receivedCurrency = strtoupper((string) data_get($capture, 'amount.currency_code', ''));

        if (($payload['status'] ?? null) !== 'COMPLETED'
            || ($capture['status'] ?? null) !== 'COMPLETED'
            || $receivedCurrency !== strtoupper((string) $payment->currency_code)
            || Money::toMinor($receivedAmount, 100) !== Money::toMinor((string) $payment->amount, 100)) {
            throw new RuntimeException('PayPal capture details do not match the booking payment.');
        }

        $payment = DB::transaction(function () use ($payment, $capture, $payload): Payment {
            $locked = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $locked->forceFill([
                'status' => Payment::STATUS_PAID,
                'gateway_transaction_id' => (string) ($capture['id'] ?? $locked->gateway_transaction_id),
                'paid_at' => data_get($capture, 'create_time') ?: now(),
                'gateway_payload' => $this->safePayload($payload),
                'failure_reason' => null,
            ])->save();

            return $locked;
        }, 3);

        $this->synchronizer->sync($payment->booking_id);

        return $payment->fresh(['booking', 'paymentMethod']);
    }

    private function accessToken(): string
    {
        $response = Http::baseUrl($this->baseUrl())
            ->acceptJson()
            ->asForm()
            ->withBasicAuth((string) config('services.paypal.client_id'), (string) config('services.paypal.secret'))
            ->timeout((int) config('services.paypal.timeout', 20))
            ->post('/v1/oauth2/token', ['grant_type' => 'client_credentials']);
        $response->throw();
        $token = (string) $response->json('access_token');

        if ($token === '') {
            throw new RuntimeException('PayPal authentication did not return an access token.');
        }

        return $token;
    }

    private function request(string $token): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())
            ->acceptJson()
            ->asJson()
            ->withToken($token)
            ->timeout((int) config('services.paypal.timeout', 20));
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('services.paypal.base_url', 'https://api-m.sandbox.paypal.com'), '/');
    }

    private function ensureConfigured(): void
    {
        if (! config('services.paypal.enabled')
            || blank(config('services.paypal.client_id'))
            || blank(config('services.paypal.secret'))) {
            throw new RuntimeException('PayPal is not configured.');
        }
    }

    private function safePayload(array $payload): array
    {
        unset($payload['payment_source']);

        return $payload;
    }
}
