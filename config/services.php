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
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],


    'apipi' => [
        'base_url'    => env('APIPI_BASE_URL', 'https://apipi.greenenergiutama.co.id'),
        'email'       => env('APIPI_EMAIL'),
        'password'    => env('APIPI_PASSWORD'),
        'timeout'     => (int) env('APIPI_TIMEOUT', 30),
        'verify'      => filter_var(env('APIPI_VERIFY_TLS', true), FILTER_VALIDATE_BOOLEAN),
        'session_ttl' => (int) env('APIPI_SESSION_TTL', 1500),
        'throttle_ms' => (int) env('APIPI_THROTTLE_MS', 300),
        'cache_path'  => env('APIPI_CACHE_PATH') ?: storage_path('app/apipi/cache'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
