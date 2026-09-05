<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\Payments\PaymobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class PaymobPaymentController extends Controller
{
    public function create(Request $request, string $bookingNumber, PaymobService $paymob): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'payment_type' => ['nullable', 'in:full,deposit,installment'],
            'amount' => ['nullable', 'numeric', 'gt:0'],
        ]);

        $booking = Booking::query()
            ->with('client')
            ->where('booking_number', $bookingNumber)
            ->firstOrFail();

        if (! $booking->client
            || strcasecmp((string) $booking->client->email, (string) $data['email']) !== 0) {
            abort(403, 'Booking verification failed.');
        }

        if (in_array($booking->status, ['cancelled', 'completed'], true)) {
            return response()->json(['message' => 'This booking cannot accept payment.'], 409);
        }

        try {
            $payment = $paymob->createCheckout(
                $booking,
                $data['payment_type'] ?? 'full',
                $data['amount'] ?? null,
            );
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to create Paymob checkout.',
            ], 422);
        }

        return response()->json([
            'payment_reference' => $payment->transaction_reference,
            'checkout_url' => $payment->checkout_url,
            'status' => $payment->status,
            'amount' => $payment->amount,
            'currency' => $payment->currency_code,
            'status_token' => $this->statusToken((string) $payment->transaction_reference),
        ], 201);
    }

    public function status(
        Request $request,
        string $paymentReference,
        PaymobService $paymob,
    ): JsonResponse {
        $request->validate([
            'token' => ['required', 'string'],
            'refresh' => ['nullable', 'boolean'],
        ]);

        if (! hash_equals(
            $this->statusToken($paymentReference),
            (string) $request->query('token')
        )) {
            abort(403);
        }

        $payment = Payment::query()
            ->where('transaction_reference', $paymentReference)
            ->firstOrFail();

        if ($request->boolean('refresh')
            && $payment->status === Payment::STATUS_PENDING
            && $payment->isPaymob()) {
            try {
                $payment = $paymob->reconcile($payment);
            } catch (\Throwable $exception) {
                report($exception);
                $payment->refresh();
            }
        }

        return response()->json([
            'payment_reference' => $payment->transaction_reference,
            'status' => $payment->status,
            'amount' => $payment->amount,
            'refunded_amount' => $payment->refunded_amount,
            'currency' => $payment->currency_code,
            'paid_at' => $payment->paid_at?->toIso8601String(),
        ]);
    }

    public function webhook(Request $request, PaymobService $paymob): JsonResponse
    {
        $receivedHmac = (string) (
            $request->query('hmac')
            ?: $request->header('X-Paymob-Hmac-Signature')
            ?: $request->input('hmac')
            ?: ''
        );

        $payload = $request->all();

        if (! $paymob->verifyHmac($payload, $receivedHmac)) {
            return response()->json(['message' => 'Invalid Paymob HMAC.'], 401);
        }

        try {
            $payment = $paymob->processWebhook($payload);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Paymob callback was rejected.',
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'payment_reference' => $payment->transaction_reference,
            'status' => $payment->status,
        ]);
    }

    public function returnFromCheckout(Request $request): RedirectResponse
    {
        $reference = (string) $request->query('reference', '');

        // UX only. Never trust browser redirect to mark a payment paid.
        if ($reference !== '' && Payment::query()->where('transaction_reference', $reference)->exists()) {
            return redirect(URL::temporarySignedRoute('website.checkout.status', now()->addHours(24), [
                'paymentReference' => $reference,
                'result' => 'processing',
            ]));
        }

        return redirect()->route('website.home');
    }

    private function statusToken(string $reference): string
    {
        return hash_hmac('sha256', $reference, (string) config('app.key'));
    }
}
