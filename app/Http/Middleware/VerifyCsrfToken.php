<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/*',
        // Auth
        'api/v1/auth/register',
        'api/v1/auth/login',
        'api/v1/auth/social-login',
        'api/v1/auth/send-otp',
        'api/v1/auth/reset-password',
        'api/v1/auth/verify-otp',
        'api/v1/auth/change-password',
        'api/v1/auth/logout',

        // Addresses
        'api/v1/addresses',

        // Cart
        'api/v1/cart/add',

        // Favorites
        'api/v1/favorites/toggle',

        // Orders
        'api/v1/order',
        'api/v1/order/cancel/*',
        'api/v1/coupon/apply',

        // Contact
        'api/v1/contact-us',

        // Paymob
        'api/v1/paymob/webhook',
    ];
}
