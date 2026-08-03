<?php

declare(strict_types=1);

return [
    'environment' => env('PESAPAL_ENVIRONMENT', 'sandbox'),

    'consumer_key' => env('PESAPAL_CONSUMER_KEY'),
    'consumer_secret' => env('PESAPAL_CONSUMER_SECRET'),

    'notification_id' => env('PESAPAL_NOTIFICATION_ID'),
    'ipn_url' => env('PESAPAL_IPN_URL'),
    'callback_url' => env('PESAPAL_CALLBACK_URL'),
    'cancellation_url' => env('PESAPAL_CANCELLATION_URL'),
    'currency' => env('PESAPAL_CURRENCY', 'KES'),

    'urls' => [
        'sandbox' => 'https://cybqa.pesapal.com/pesapalv3/api',
        'production' => 'https://pay.pesapal.com/v3/api',
    ],

    'http' => [
        'timeout' => (float) env('PESAPAL_TIMEOUT', 30),
        'connect_timeout' => (float) env('PESAPAL_CONNECT_TIMEOUT', 10),
        'verify' => filter_var(env('PESAPAL_VERIFY_SSL', true), FILTER_VALIDATE_BOOL),
        'user_agent' => env('PESAPAL_USER_AGENT', 'shiftechafrica-pesapal-laravel-sdk/1.0'),
    ],

    'token_cache' => [
        'enabled' => filter_var(env('PESAPAL_TOKEN_CACHE', true), FILTER_VALIDATE_BOOL),
        'key' => env('PESAPAL_TOKEN_CACHE_KEY', 'pesapal.api.v3.access_token'),
        'safety_seconds' => (int) env('PESAPAL_TOKEN_SAFETY_SECONDS', 30),
    ],
];
