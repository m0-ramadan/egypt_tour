<?php

return [

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie'
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',
        'http://72.62.25.136:3000',
        'https://www.talaaljazeera.com',
        'https://ecommerce-xo.vercel.app',
        'https://talaaljazeera.com',
        'https://www.talaaljazeera.com',
    ],

    // علشان Vercel preview
    'allowed_origins_patterns' => [
        '#^https://.*\.vercel\.app$#'
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
