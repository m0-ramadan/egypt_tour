<?php

namespace App\Traits;

use App\Services\Payments\PaymobService;
use Illuminate\Http\Request;

/**
 * Legacy compatibility shim.
 *
 * TravelNest booking payments now use Paymob Intention API + Unified Checkout
 * through PaymobPaymentController. The old ecommerce payment-link flow is
 * intentionally disabled so it cannot bypass booking/payment integrity.
 */
trait HandlesPaymobPayment
{
    public function initiatePaymobPayment($order): array
    {
        return [
            'success' => false,
            'message' => 'Legacy Paymob order checkout is disabled. Use the Booking Paymob checkout flow.',
        ];
    }

    public function verifyPaymobHmac(array $payload): bool
    {
        $hmac = $payload['hmac'] ?? null;

        return app(PaymobService::class)->verifyHmac($payload, is_string($hmac) ? $hmac : null);
    }

    public function handlePaymobWebhook(Request $request)
    {
        $receivedHmac = (string) (
            $request->query('hmac')
            ?: $request->header('X-Paymob-Hmac-Signature')
            ?: $request->input('hmac')
            ?: ''
        );

        $service = app(PaymobService::class);

        if (! $service->verifyHmac($request->all(), $receivedHmac)) {
            return response('Invalid HMAC', 401);
        }

        return response(
            'Legacy order Paymob webhook is disabled. Use /api/v1/paymob/webhook.',
            410
        );
    }
}
