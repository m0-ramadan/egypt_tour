<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Translation AI Primary & Fallback Providers
    |--------------------------------------------------------------------------
    */
    'primary_provider' => env('TRANSLATION_PRIMARY_PROVIDER', 'gemini'),

    'fallback_provider' => env('TRANSLATION_FALLBACK_PROVIDER', 'deepseek'),

    /*
    |--------------------------------------------------------------------------
    | Google Gemini Configuration
    |--------------------------------------------------------------------------
    */
    'google' => [
        'api_key' => env('GOOGLE_AI_API_KEY'),
        'model' => env('GOOGLE_AI_MODEL', 'gemini-2.5-flash'),
        'thinking_budget' => 0, // Mandatory 0 for translation tasks
        'timeout' => (int) env('GOOGLE_AI_TIMEOUT', 30),
        'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models',
    ],

    /*
    |--------------------------------------------------------------------------
    | DeepSeek Configuration
    |--------------------------------------------------------------------------
    */
    'deepseek' => [
        'api_key' => env('DEEPSEEK_API_KEY'),
        'api_url' => env('DEEPSEEK_API_URL', env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com/v1/chat/completions')),
        'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
        'timeout' => (int) env('DEEPSEEK_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Optimization & Batching Rules
    |--------------------------------------------------------------------------
    */
    'low_token_mode' => (bool) env('TRANSLATION_LOW_TOKEN_MODE', true),

    'max_chars_per_request' => (int) env('TRANSLATION_MAX_CHARS_PER_REQUEST', 2500),

    'max_items_per_batch' => (int) env('TRANSLATION_BATCH_SIZE', 30),

    'retry_count' => (int) env('TRANSLATION_RETRY_COUNT', 1),

    'circuit_breaker_cooldown' => (int) env('TRANSLATION_FAILURE_COOLDOWN', env('DEEPSEEK_FAILURE_COOLDOWN', 3600)),

];
