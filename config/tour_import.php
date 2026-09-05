<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tour Importer Enablement
    |--------------------------------------------------------------------------
    |
    | Controls whether external tour URL importing is enabled in the system.
    |
    */
    'enabled' => (bool) env('TOUR_IMPORT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Allowed Remote Hosts (SSRF Protection)
    |--------------------------------------------------------------------------
    |
    | Only URLs matching these hosts are permitted for import.
    |
    */
    'allowed_hosts' => array_values(array_filter(array_map('trim', explode(
        ',',
        env('TOUR_IMPORT_ALLOWED_HOSTS', 'luxorandaswan.com,www.luxorandaswan.com')
    )))),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Connection Timeouts
    |--------------------------------------------------------------------------
    |
    | Timeouts in seconds for fetching the remote HTML source.
    |
    */
    'timeout' => (int) env('TOUR_IMPORT_TIMEOUT', 30),
    'connect_timeout' => (int) env('TOUR_IMPORT_CONNECT_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | User Agent
    |--------------------------------------------------------------------------
    |
    | User-Agent header sent with outgoing HTTP requests.
    |
    */
    'user_agent' => env('TOUR_IMPORT_USER_AGENT', 'TravelNest Tour Importer/1.0 (+https://travelnest.com)'),

    /*
    |--------------------------------------------------------------------------
    | Content Processing Defaults
    |--------------------------------------------------------------------------
    |
    | Defaults for AI copy rewriting, downloading media, and updating existing tours.
    |
    */
    'rewrite_content' => (bool) env('TOUR_IMPORT_REWRITE_CONTENT', true),
    'download_images' => (bool) env('TOUR_IMPORT_DOWNLOAD_IMAGES', true),
    'update_existing' => (bool) env('TOUR_IMPORT_UPDATE_EXISTING', false),

    /*
    |--------------------------------------------------------------------------
    | Media Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Target disk, path, limit, and max size in bytes for downloaded images.
    |
    */
    'image_disk' => env('TOUR_IMPORT_IMAGE_DISK', 'public'),
    'image_directory' => env('TOUR_IMPORT_IMAGE_DIRECTORY', 'packages/imported'),
    'max_images' => (int) env('TOUR_IMPORT_MAX_IMAGES', 10),
    'max_image_bytes' => (int) env('TOUR_IMPORT_MAX_IMAGE_BYTES', 5 * 1024 * 1024), // 5MB

    /*
    |--------------------------------------------------------------------------
    | Taxonomy Defaults
    |--------------------------------------------------------------------------
    |
    | Fallback currency code and primary country code.
    |
    */
    'default_currency' => env('TOUR_IMPORT_DEFAULT_CURRENCY', 'USD'),
    'default_country_code' => env('TOUR_IMPORT_DEFAULT_COUNTRY_CODE', 'EG'),
];
