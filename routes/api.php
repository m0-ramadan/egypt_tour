<?php

use App\Http\Controllers\Api\PaymobPaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('bookings/{bookingNumber}/payments/paymob', [PaymobPaymentController::class, 'create'])
        ->middleware('throttle:20,1')
        ->name('api.v1.paymob.checkout');

    Route::get('payments/{paymentReference}/status', [PaymobPaymentController::class, 'status'])
        ->middleware('throttle:60,1')
        ->name('api.v1.paymob.status');

    Route::post('paymob/webhook', [PaymobPaymentController::class, 'webhook'])
        ->name('api.v1.paymob.webhook');
});
