<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'paymob' => [
        'enabled' => filter_var(env('PAYMOB_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        'base_url' => rtrim(env('PAYMOB_BASE_URL', 'https://accept.paymob.com'), '/'),
        'checkout_url' => env('PAYMOB_CHECKOUT_URL', 'https://accept.paymob.com/unifiedcheckout/'),
        'secret_key' => env('PAYMOB_SECRET_KEY'),
        'public_key' => env('PAYMOB_PUBLIC_KEY'),
        'api_key' => env('PAYMOB_API_KEY'),
        'hmac_secret' => env('PAYMOB_HMAC_SECRET'),
        'integration_ids' => array_values(array_filter(array_map(
            static fn ($id) => is_numeric(trim($id)) ? (int) trim($id) : null,
            explode(',', (string) env('PAYMOB_INTEGRATION_IDS', ''))
        ))),
        'currency' => strtoupper(env('PAYMOB_CURRENCY', 'EGP')),
        'minor_unit_factor' => (int) env('PAYMOB_MINOR_UNIT_FACTOR', 100),
        'timeout' => (int) env('PAYMOB_TIMEOUT', 20),
        'pending_hold_minutes' => (int) env('PAYMOB_PENDING_HOLD_MINUTES', 30),
        'notification_url' => env('PAYMOB_NOTIFICATION_URL'),
        'redirection_url' => env('PAYMOB_REDIRECTION_URL'),
    ],

    'paypal' => [
        'enabled' => filter_var(env('PAYPAL_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        'mode' => env('PAYPAL_MODE', 'sandbox'),
        'base_url' => env('PAYPAL_BASE_URL', env('PAYPAL_MODE', 'sandbox') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com'),
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret' => env('PAYPAL_SECRET'),
        'timeout' => (int) env('PAYPAL_TIMEOUT', 20),
    ],

    'deepseek' => [
        'api_key' => env('DEEPSEEK_API_KEY'),
        'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
        'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com/v1/chat/completions'),
        'timeout' => (int) env('DEEPSEEK_TIMEOUT', 30),
        'auto_translate_missing' => false,
    ],

    'savvyhost' => [
        'base_url' => env('SAVVYHOST_API_URL', 'https://api.savvyhost.net'),
        'tenant' => env('SAVVYHOST_TENANT_SUBDOMAIN', 'etrotours'),
        'email' => env('SAVVYHOST_LOGIN_EMAIL'),
        'password' => env('SAVVYHOST_LOGIN_PASSWORD'),
        'token' => env('SAVVYHOST_API_TOKEN'),
    ],
];
