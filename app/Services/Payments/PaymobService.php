<?php

namespace App\Services\Payments;

use App\Models\Booking;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PaymentRefund;
use App\Support\Money;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class PaymobService
{
    private const HMAC_FIELDS = [
        'amount_cents',
        'created_at',
        'currency',
        'error_occured',
        'has_parent_transaction',
        'obj.id',
        'integration_id',
        'is_3d_secure',
        'is_auth',
        'is_capture',
        'is_refunded',
        'is_standalone_payment',
        'is_voided',
        'order.id',
        'owner',
        'pending',
        'source_data.pan',
        'source_data.sub_type',
        'source_data.type',
        'success',
    ];

    public function __construct(
        private readonly BookingPaymentSynchronizer $synchronizer,
    ) {}

    public function enabled(): bool
    {
        return (bool) config('services.paymob.enabled', false);
    }

    public function createCheckout(
        Booking $booking,
        string $paymentType = 'full',
        string|int|float|null $requestedAmount = null,
    ): Payment {
        $this->ensureCheckoutConfigured();

        $booking->loadMissing(['client', 'package']);

        if (! $booking->client) {
            throw new RuntimeException('Booking client is required before payment.');
        }

        if (! $booking->client->email || ! $booking->client->phone) {
            throw new RuntimeException('Client email and phone are required for Paymob checkout.');
        }

        $booking = $this->synchronizer->sync($booking);
        $amountMinor = $this->resolveAmountMinor($booking, $paymentType, $requestedAmount);
        $factor = $this->minorUnitFactor();
        $currency = strtoupper((string) ($booking->currency_code ?: config('services.paymob.currency', 'EGP')));
        $bookingCurrency = strtoupper((string) ($booking->currency_code ?: 'USD'));
        $gatewayCurrency = strtoupper((string) config('services.paymob.currency', $bookingCurrency));
        $method = $this->paymobPaymentMethod();

        $bookingAmountDecimal = (float) Money::fromMinor($amountMinor, $factor);
        if ($gatewayCurrency !== $bookingCurrency) {
            $convertedDecimal = Currency::convert($bookingAmountDecimal, $bookingCurrency, $gatewayCurrency);
            $paymobAmountMinor = Money::toMinor($convertedDecimal, $factor);
            $currency = $gatewayCurrency;
        } else {
            $paymobAmountMinor = $amountMinor;
            $currency = $bookingCurrency;
        }

        $payment = DB::transaction(function () use (
            $booking,
            $paymentType,
            $amountMinor,
            $paymobAmountMinor,
            $factor,
            $currency,
            $bookingCurrency,
            $method,
        ): Payment {
            /** @var Booking $locked */
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if (in_array($locked->status, ['cancelled', 'completed'], true)) {
                throw new RuntimeException('This booking cannot accept a new payment.');
            }

            // Reserve outstanding balance against recent pending Paymob attempts.
            // This prevents double-clicks/concurrent requests from creating two full
            // checkout attempts for the same money while still allowing abandoned
            // attempts to age out and be reconciled.
            $lockedTotalMinor = Money::toMinor((string) $locked->total_amount, $factor);
            $lockedPaidMinor = Money::toMinor((string) $locked->paid_amount, $factor);
            $outstandingMinor = max(0, $lockedTotalMinor - $lockedPaidMinor);
            $holdMinutes = max(1, (int) config('services.paymob.pending_hold_minutes', 30));
            $pendingMinor = 0;

            $pendingAttempts = Payment::query()
                ->where('booking_id', $locked->id)
                ->where('payment_method_id', $method->id)
                ->where('status', Payment::STATUS_PENDING)
                ->where('created_at', '>=', now()->subMinutes($holdMinutes))
                ->lockForUpdate()
                ->get(['amount', 'currency_code']);

            foreach ($pendingAttempts as $pendingAttempt) {
                $pCurrency = strtoupper((string) ($pendingAttempt->currency_code ?: $bookingCurrency));
                $pAmount = (float) $pendingAttempt->amount;
                if ($pCurrency !== $bookingCurrency) {
                    $pAmount = Currency::convert($pAmount, $pCurrency, $bookingCurrency);
                }
                $pendingMinor += Money::toMinor((string) $pAmount, $factor);
            }

            $availableMinor = max(0, $outstandingMinor - $pendingMinor);

            if ($amountMinor > $availableMinor) {
                throw new RuntimeException(
                    'Another Paymob payment attempt is already pending for this booking. '
                        . 'Wait for its status to settle or reconciliation to run before retrying.'
                );
            }

            $reference = 'PAY-' . strtoupper((string) Str::uuid());

            return Payment::create([
                'booking_id' => $locked->id,
                'payment_method_id' => $method->id,
                'transaction_reference' => $reference,
                'amount' => Money::fromMinor($amountMinor, $factor),
                'amount' => Money::fromMinor($paymobAmountMinor, $factor),
                'currency_code' => $currency,
                'status' => Payment::STATUS_PENDING,
                'payment_type' => $paymentType,
                'refunded_amount' => '0.00',
            ]);
        }, 3);

        try {
            $response = $this->secretRequest()->post('/v1/intention/', $this->intentionPayload($booking, $payment));
            $response->throw();

            $data = (array) $response->json();
            $clientSecret = (string) ($data['client_secret'] ?? '');

            if ($clientSecret === '') {
                throw new RuntimeException('Paymob intention response is missing client_secret.');
            }

            $checkoutUrl = rtrim((string) config(
                'services.paymob.checkout_url',
                'https://accept.paymob.com/unifiedcheckout/'
            ), '/') . '/?publicKey=' . rawurlencode((string) config('services.paymob.public_key'))
                . '&clientSecret=' . rawurlencode($clientSecret);

            $payment->forceFill([
                'gateway_intention_id' => (string) ($data['id'] ?? ''),
                'gateway_order_id' => (string) ($data['intention_order_id'] ?? ''),
                'gateway_reference' => (string) ($data['id'] ?? ''),
                'checkout_url' => $checkoutUrl,
                'gateway_payload' => $this->sanitizeGatewayPayload($data),
                'failure_reason' => null,
            ])->save();

            return $payment->fresh(['booking', 'paymentMethod']);
        } catch (\Throwable $exception) {
            $payment->forceFill([
                'status' => Payment::STATUS_FAILED,
                'failure_reason' => $exception->getMessage(),
            ])->save();

            $this->synchronizer->sync($booking);

            throw $exception;
        }
    }

    public function verifyHmac(array $payload, ?string $receivedHmac): bool
    {
        $secret = (string) config('services.paymob.hmac_secret');

        if ($secret === '' || ! is_string($receivedHmac) || $receivedHmac === '') {
            return false;
        }

        $obj = is_array($payload['obj'] ?? null) ? $payload['obj'] : $payload;

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
            is_array($obj['source_data'] ?? null) ? ($obj['source_data']['pan'] ?? '') : '',
            is_array($obj['source_data'] ?? null) ? ($obj['source_data']['sub_type'] ?? '') : '',
            is_array($obj['source_data'] ?? null) ? ($obj['source_data']['type'] ?? '') : '',
            $obj['success'] ?? '',
        ];

        $canonical = implode('', array_map([$this, 'paymobString'], $values));
        $calculated = hash_hmac('sha512', $canonical, $secret);

        return hash_equals(strtolower($calculated), strtolower($receivedHmac));
    }

    public function processWebhook(array $payload): Payment
    {
        $obj = is_array($payload['obj'] ?? null) ? $payload['obj'] : $payload;

        return $this->applyTransaction($obj, false);
    }

    public function reconcile(Payment $payment): Payment
    {
        if (! $payment->isPaymob()) {
            throw new RuntimeException('Only Paymob payments can be reconciled.');
        }

        $authToken = $this->authToken();

        $body = [
            'auth_token' => $authToken,
            'merchant_order_id' => (string) $payment->transaction_reference,
        ];

        if ($payment->gateway_order_id) {
            $body['order_id'] = (string) $payment->gateway_order_id;
        }

        $response = $this->baseRequest()->post('/api/ecommerce/orders/transaction_inquiry', $body);
        $response->throw();

        $payload = (array) $response->json();

        $payment->increment('reconciliation_attempts');
        $payment->forceFill(['last_reconciled_at' => now()])->save();

        return $this->applyTransaction($payload, true);
    }

    public function refund(Payment $payment, string|int|float|null $requestedAmount = null, ?int $requestedBy = null): PaymentRefund
    {
        if (! $payment->isPaymob()) {
            throw new RuntimeException('Only Paymob payments can use the Paymob refund API.');
        }

        if (! in_array($payment->status, [
            Payment::STATUS_PAID,
            Payment::STATUS_PARTIALLY_REFUNDED,
        ], true)) {
            throw new RuntimeException('This Paymob payment is not refundable in its current state.');
        }

        if (! $payment->gateway_transaction_id) {
            $payment = $this->reconcile($payment);
        }

        if (! $payment->gateway_transaction_id) {
            throw new RuntimeException('Paymob transaction id is unavailable after reconciliation.');
        }

        $factor = $this->minorUnitFactor();

        $refund = DB::transaction(function () use ($payment, $requestedAmount, $requestedBy, $factor): PaymentRefund {
            /** @var Payment $locked */
            $locked = Payment::query()
                ->with('refunds')
                ->lockForUpdate()
                ->findOrFail($payment->id);

            $paymentMinor = Money::toMinor((string) $locked->amount, $factor);
            $reservedMinor = 0;

            foreach (
                $locked->refunds()
                    ->whereIn('status', [PaymentRefund::STATUS_PENDING, PaymentRefund::STATUS_SUCCEEDED])
                    ->lockForUpdate()
                    ->get() as $existing
            ) {
                $reservedMinor += Money::toMinor((string) $existing->amount, $factor);
            }

            $remainingMinor = max(0, $paymentMinor - $reservedMinor);
            $refundMinor = $requestedAmount === null
                ? $remainingMinor
                : Money::toMinor($requestedAmount, $factor);

            if ($refundMinor <= 0 || $refundMinor > $remainingMinor) {
                throw new RuntimeException('Refund amount exceeds the remaining refundable amount.');
            }

            $samePendingExists = $locked->refunds()
                ->where('status', PaymentRefund::STATUS_PENDING)
                ->where('amount', Money::fromMinor($refundMinor, $factor))
                ->where('created_at', '>=', now()->subMinutes(5))
                ->exists();

            if ($samePendingExists) {
                throw new RuntimeException('An identical refund is already pending.');
            }

            return PaymentRefund::create([
                'payment_id' => $locked->id,
                'refund_reference' => 'RFD-' . strtoupper((string) Str::uuid()),
                'amount' => Money::fromMinor($refundMinor, $factor),
                'currency_code' => strtoupper((string) $locked->currency_code),
                'status' => PaymentRefund::STATUS_PENDING,
                'requested_by' => $requestedBy,
                'requested_at' => now(),
            ]);
        }, 3);

        try {
            $response = $this->secretRequest()->post('/api/acceptance/void_refund/refund', [
                'transaction_id' => (int) $payment->gateway_transaction_id,
                'amount_cents' => Money::toMinor((string) $refund->amount, $factor),
            ]);
            $response->throw();

            $data = (array) $response->json();

            if (array_key_exists('success', $data) && ! filter_var($data['success'], FILTER_VALIDATE_BOOLEAN)) {
                throw new RuntimeException((string) Arr::get($data, 'data.message', 'Paymob refund was rejected.'));
            }

            $pending = (bool) ($data['pending'] ?? false);
            $refund->forceFill([
                'gateway_refund_id' => isset($data['id']) ? (string) $data['id'] : null,
                'status' => $pending ? PaymentRefund::STATUS_PENDING : PaymentRefund::STATUS_SUCCEEDED,
                'processed_at' => $pending ? null : now(),
                'gateway_payload' => $this->sanitizeGatewayPayload($data),
                'failure_reason' => null,
            ])->save();

            if (! $pending) {
                $this->applySuccessfulRefundToPayment($payment, $refund);
            }

            return $refund->fresh();
        } catch (\Throwable $exception) {
            $ambiguous = $this->isAmbiguousRefundFailure($exception);

            $refund->forceFill([
                // A timeout/5xx can happen after Paymob has already accepted the
                // refund. Keep it pending so a retry cannot refund the money twice;
                // reconciliation will settle it from gateway aggregate state.
                'status' => $ambiguous
                    ? PaymentRefund::STATUS_PENDING
                    : PaymentRefund::STATUS_FAILED,
                'processed_at' => $ambiguous ? null : now(),
                'failure_reason' => ($ambiguous ? 'Refund outcome unknown; reconciliation required. ' : '')
                    . $exception->getMessage(),
            ])->save();

            throw $exception;
        }
    }

    private function applyTransaction(array $obj, bool $fromReconciliation): Payment
    {
        $payment = $this->findPaymentForTransaction($obj);

        if (! $payment) {
            throw new RuntimeException('No local payment matches this Paymob transaction.');
        }

        $factor = $this->minorUnitFactor();
        $incomingTransactionId = isset($obj['id']) ? (string) $obj['id'] : null;

        $updated = DB::transaction(function () use (
            $payment,
            $obj,
            $factor,
            $incomingTransactionId,
            $fromReconciliation,
        ): Payment {
            /** @var Payment $locked */
            $locked = Payment::query()
                ->with(['booking', 'refunds'])
                ->lockForUpdate()
                ->findOrFail($payment->id);

            $this->assertTransactionMatches($locked, $obj);

            $pending = filter_var($obj['pending'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $success = filter_var($obj['success'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $isRefunded = filter_var($obj['is_refunded'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $incomingStatus = $pending
                ? Payment::STATUS_PENDING
                : ($success ? Payment::STATUS_PAID : Payment::STATUS_FAILED);

            // A stale failure/pending callback may never regress a settled payment.
            if (in_array($locked->status, [
                Payment::STATUS_PAID,
                Payment::STATUS_PARTIALLY_REFUNDED,
                Payment::STATUS_REFUNDED,
            ], true) && ! $success) {
                $incomingStatus = $locked->status;
            }

            $refundedMinor = isset($obj['refunded_amount_cents'])
                ? max(0, (int) $obj['refunded_amount_cents'])
                : Money::toMinor((string) ($locked->refunded_amount ?? 0), $factor);

            $amountMinor = Money::toMinor((string) $locked->amount, $factor);

            if ($isRefunded || $refundedMinor > 0) {
                if ($refundedMinor >= $amountMinor) {
                    $incomingStatus = Payment::STATUS_REFUNDED;
                    $refundedMinor = $amountMinor;
                } else {
                    $incomingStatus = Payment::STATUS_PARTIALLY_REFUNDED;
                }

                // If a refund request timed out after Paymob accepted it, its local
                // row intentionally remains pending. Transaction inquiry exposes the
                // aggregate refunded amount, so use that authoritative total to settle
                // pending rows without issuing a second refund request.
                $this->settlePendingRefundRowsFromGatewayTotal($locked, $refundedMinor, $factor);
            }

            $order = is_array($obj['order'] ?? null)
                ? $obj['order']
                : ['id' => $obj['order'] ?? null];

            $changes = [
                'status' => $incomingStatus,
                'gateway_payload' => $this->sanitizeGatewayPayload($obj),
                'failure_reason' => $incomingStatus === Payment::STATUS_FAILED
                    ? (string) Arr::get($obj, 'data.message', 'Paymob transaction failed.')
                    : null,
                'refunded_amount' => Money::fromMinor($refundedMinor, $factor),
            ];

            if ($success) {
                $changes['gateway_transaction_id'] = $incomingTransactionId ?: $locked->gateway_transaction_id;
                $changes['gateway_reference'] = $incomingTransactionId ?: $locked->gateway_reference;
                $changes['gateway_order_id'] = (string) ($order['id'] ?? $locked->gateway_order_id);
                $changes['paid_at'] = $obj['paid_at'] ?? $locked->paid_at ?? now();
            } elseif (! $locked->gateway_transaction_id && $incomingTransactionId) {
                $changes['gateway_transaction_id'] = $incomingTransactionId;
            }

            if ($incomingStatus === Payment::STATUS_REFUNDED) {
                $changes['refunded_at'] = $locked->refunded_at ?? now();
            }

            if ($fromReconciliation) {
                $changes['last_reconciled_at'] = now();
            }

            $locked->forceFill($changes)->save();

            return $locked->fresh(['booking', 'paymentMethod', 'refunds']);
        }, 3);

        $this->synchronizer->sync($updated->booking_id);

        return $updated->fresh(['booking', 'paymentMethod', 'refunds']);
    }

    private function applySuccessfulRefundToPayment(Payment $payment, PaymentRefund $refund): void
    {
        $factor = $this->minorUnitFactor();

        $bookingId = DB::transaction(function () use ($payment, $refund, $factor): int {
            /** @var Payment $locked */
            $locked = Payment::query()
                ->with('refunds')
                ->lockForUpdate()
                ->findOrFail($payment->id);

            $succeededMinor = 0;

            foreach (
                $locked->refunds()
                    ->where('status', PaymentRefund::STATUS_SUCCEEDED)
                    ->get() as $row
            ) {
                $succeededMinor += Money::toMinor((string) $row->amount, $factor);
            }

            $amountMinor = Money::toMinor((string) $locked->amount, $factor);
            $succeededMinor = min($succeededMinor, $amountMinor);

            $locked->forceFill([
                'refunded_amount' => Money::fromMinor($succeededMinor, $factor),
                'refunded_at' => $succeededMinor >= $amountMinor ? now() : $locked->refunded_at,
                'status' => $succeededMinor >= $amountMinor
                    ? Payment::STATUS_REFUNDED
                    : Payment::STATUS_PARTIALLY_REFUNDED,
            ])->save();

            return $locked->booking_id;
        }, 3);

        $this->synchronizer->sync($bookingId);
    }


    private function settlePendingRefundRowsFromGatewayTotal(
        Payment $payment,
        int $gatewayRefundedMinor,
        int $factor,
    ): void {
        if ($gatewayRefundedMinor <= 0) {
            return;
        }

        $knownSucceededMinor = 0;
        foreach (
            $payment->refunds()
                ->where('status', PaymentRefund::STATUS_SUCCEEDED)
                ->lockForUpdate()
                ->get() as $succeeded
        ) {
            $knownSucceededMinor += Money::toMinor((string) $succeeded->amount, $factor);
        }

        $unattributedMinor = max(0, $gatewayRefundedMinor - $knownSucceededMinor);
        if ($unattributedMinor <= 0) {
            return;
        }

        foreach (
            $payment->refunds()
                ->where('status', PaymentRefund::STATUS_PENDING)
                ->oldest()
                ->lockForUpdate()
                ->get() as $pendingRefund
        ) {
            $rowMinor = Money::toMinor((string) $pendingRefund->amount, $factor);

            if ($rowMinor <= 0 || $rowMinor > $unattributedMinor) {
                continue;
            }

            $pendingRefund->forceFill([
                'status' => PaymentRefund::STATUS_SUCCEEDED,
                'processed_at' => now(),
                'failure_reason' => null,
            ])->save();

            $unattributedMinor -= $rowMinor;

            if ($unattributedMinor <= 0) {
                break;
            }
        }
    }

    private function isAmbiguousRefundFailure(\Throwable $exception): bool
    {
        if ($exception instanceof ConnectionException) {
            return true;
        }

        return $exception instanceof RequestException
            && $exception->response->serverError();
    }

    private function findPaymentForTransaction(array $obj): ?Payment
    {
        $order = is_array($obj['order'] ?? null) ? $obj['order'] : [];
        $reference = $order['merchant_order_id']
            ?? $obj['merchant_order_id']
            ?? $obj['special_reference']
            ?? Arr::get($obj, 'extras.payment_reference')
            ?? null;

        $transactionId = isset($obj['id']) ? (string) $obj['id'] : null;

        return Payment::query()
            ->where(function ($query) use ($reference, $transactionId, $order) {
                if ($reference) {
                    $query->where('transaction_reference', (string) $reference);
                } elseif ($transactionId) {
                    $query->where('gateway_transaction_id', $transactionId);
                } elseif (isset($order['id'])) {
                    $query->where('gateway_order_id', (string) $order['id']);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->first();
    }

    private function assertTransactionMatches(Payment $payment, array $obj): void
    {
        $factor = $this->minorUnitFactor();
        $expectedAmount = Money::toMinor((string) $payment->amount, $factor);
        $receivedAmount = (int) ($obj['amount_cents'] ?? -1);

        if ($receivedAmount !== $expectedAmount) {
            throw new RuntimeException('Paymob amount mismatch.');
        }

        $receivedCurrency = strtoupper((string) ($obj['currency'] ?? ''));
        if ($receivedCurrency !== strtoupper((string) $payment->currency_code)) {
            throw new RuntimeException('Paymob currency mismatch.');
        }

        $integrationIds = array_map('intval', (array) config('services.paymob.integration_ids', []));
        if ($integrationIds !== []) {
            $incomingIntegration = (int) ($obj['integration_id'] ?? 0);

            if (! in_array($incomingIntegration, $integrationIds, true)) {
                throw new RuntimeException('Paymob integration id mismatch.');
            }
        }
    }

    private function resolveAmountMinor(
        Booking $booking,
        string $paymentType,
        string|int|float|null $requestedAmount,
    ): int {
        if (! in_array($paymentType, ['full', 'deposit', 'installment'], true)) {
            throw new RuntimeException('Unsupported payment type.');
        }

        $factor = $this->minorUnitFactor();
        $totalMinor = Money::toMinor((string) $booking->total_amount, $factor);
        $paidMinor = Money::toMinor((string) $booking->paid_amount, $factor);
        $remainingMinor = max(0, $totalMinor - $paidMinor);

        if ($remainingMinor <= 0) {
            throw new RuntimeException('Booking has no outstanding balance.');
        }

        if ($paymentType === 'full') {
            return $remainingMinor;
        }

        if ($requestedAmount !== null) {
            $requestedMinor = Money::toMinor($requestedAmount, $factor);

            if ($requestedMinor <= 0 || $requestedMinor > $remainingMinor) {
                throw new RuntimeException('Requested payment amount is invalid.');
            }

            return $requestedMinor;
        }

        if ($paymentType === 'installment') {
            throw new RuntimeException('Installment amount is required.');
        }

        $package = $booking->package;

        if (! $package || $package->deposit_policy !== 'required' || ! $package->deposit_value) {
            throw new RuntimeException('This package does not define a required deposit amount.');
        }

        if ($package->deposit_type === 'percent') {
            $percentage = Money::toMinor((string) $package->deposit_value, 100);
            $depositMinor = intdiv($totalMinor * $percentage, 10000);
        } elseif ($package->deposit_type === 'fixed') {
            $depositMinor = Money::toMinor((string) $package->deposit_value, $factor);
        } else {
            throw new RuntimeException('Package deposit type is invalid.');
        }

        return max(1, min($depositMinor, $remainingMinor));
    }

    private function intentionPayload(Booking $booking, Payment $payment): array
    {
        $factor = $this->minorUnitFactor();
        $amount = Money::toMinor((string) $payment->amount, $factor);
        $client = $booking->client;

        $notificationUrl = (string) config('services.paymob.notification_url');
        if ($notificationUrl === '') {
            $notificationUrl = route('api.v1.paymob.webhook');
        }

        $redirectionUrl = (string) config('services.paymob.redirection_url');
        if ($redirectionUrl === '') {
            $redirectionUrl = route('paymob.return', ['reference' => $payment->transaction_reference]);
        }

        return [
            'amount' => $amount,
            'currency' => strtoupper((string) $payment->currency_code),
            'payment_methods' => array_values(array_map('intval', (array) config('services.paymob.integration_ids', []))),
            'items' => [[
                'name' => 'Booking ' . $booking->booking_number,
                'amount' => $amount,
                'description' => 'TravelNest booking payment',
                'quantity' => 1,
            ]],
            'billing_data' => [
                'apartment' => 'NA',
                'first_name' => (string) ($client->first_name ?: 'NA'),
                'last_name' => (string) ($client->last_name ?: 'NA'),
                'street' => 'NA',
                'building' => 'NA',
                'phone_number' => (string) $client->phone,
                'city' => 'NA',
                'country' => 'EGY',
                'email' => (string) $client->email,
                'floor' => 'NA',
                'state' => 'NA',
                'postal_code' => 'NA',
                'extra_description' => 'TravelNest',
            ],
            'extras' => [
                'booking_number' => $booking->booking_number,
                'payment_reference' => $payment->transaction_reference,
            ],
            'special_reference' => $payment->transaction_reference,
            'notification_url' => $notificationUrl,
            'redirection_url' => $redirectionUrl,
        ];
    }

    private function paymobPaymentMethod(): PaymentMethod
    {
        $method = PaymentMethod::query()
            ->where(function ($query) {
                $query->where('code', 'paymob')
                    ->orWhere('provider', 'paymob');
            })
            ->first();

        if (! $method) {
            throw new RuntimeException('Paymob payment method is not registered.');
        }

        if (! $method->is_active) {
            throw new RuntimeException('Paymob payment method is disabled.');
        }

        return $method;
    }

    private function ensureCheckoutConfigured(): void
    {
        if (! $this->enabled()) {
            throw new RuntimeException('Paymob is disabled.');
        }

        foreach (['secret_key', 'public_key', 'hmac_secret'] as $key) {
            if ((string) config('services.paymob.' . $key) === '') {
                throw new RuntimeException('Missing Paymob configuration: ' . $key);
            }
        }

        if ((array) config('services.paymob.integration_ids', []) === []) {
            throw new RuntimeException('PAYMOB_INTEGRATION_IDS is empty.');
        }
    }

    private function authToken(): string
    {
        $apiKey = (string) config('services.paymob.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('PAYMOB_API_KEY is required for transaction reconciliation.');
        }

        $response = $this->baseRequest()->post('/api/auth/tokens', [
            'api_key' => $apiKey,
        ]);
        $response->throw();

        $token = (string) $response->json('token');

        if ($token === '') {
            throw new RuntimeException('Paymob authentication response did not include a token.');
        }

        return $token;
    }

    private function secretRequest(): PendingRequest
    {
        $secret = (string) config('services.paymob.secret_key');

        if ($secret === '') {
            throw new RuntimeException('PAYMOB_SECRET_KEY is not configured.');
        }

        return $this->baseRequest()->withToken($secret, 'Token');
    }

    private function baseRequest(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.paymob.base_url', 'https://accept.paymob.com'), '/'))
            ->acceptJson()
            ->asJson()
            // Do not auto-retry non-idempotent Paymob POSTs (Intention/Refund).
            // Ambiguous failures are handled by local state + reconciliation instead.
            ->timeout((int) config('services.paymob.timeout', 20));
    }

    private function minorUnitFactor(): int
    {
        return max(1, (int) config('services.paymob.minor_unit_factor', 100));
    }

    private function paymobString(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return '';
        }

        return (string) $value;
    }

    private function sanitizeGatewayPayload(array $payload): array
    {
        $blocked = [
            'client_secret',
            'secret_key',
            'api_key',
            'hmac_secret',
            'auth_token',
            'token',
            'card_token',
            'card_num',
            'cvv',
            'cvc',
        ];

        $walk = function (array $data) use (&$walk, $blocked): array {
            $clean = [];

            foreach ($data as $key => $value) {
                if (in_array(strtolower((string) $key), $blocked, true)) {
                    continue;
                }

                $clean[$key] = is_array($value) ? $walk($value) : $value;
            }

            return $clean;
        };

        return $walk($payload);
    }
}
