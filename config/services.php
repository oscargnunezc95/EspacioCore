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
    'mercadopago' => [
        'token'          => env('MERCADOPAGO_ACCESS_TOKEN'),
        'platform_token' => env('MERCADOPAGO_PLATFORM_TOKEN'), // Token del dueño de la plataforma (para cobro de facturas)
        'public_key'     => env('MERCADOPAGO_PUBLIC_KEY'),
        'app_id'         => env('MERCADOPAGO_APP_ID'),
        'secret'         => env('MERCADOPAGO_CLIENT_SECRET'),
        'redirect_uri'   => env('MERCADOPAGO_REDIRECT_URI'),
        'webhook_domain' => env('MERCADOPAGO_WEBHOOK_DOMAIN'),
        'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET'),
        // Las URLs base de la API que parametrizamos
        'auth_url'       => env('MERCADOPAGO_AUTH_URL', 'https://auth.mercadopago.cl/authorization'),
        'api_url'        => env('MERCADOPAGO_API_URL', 'https://api.mercadopago.com'),
    ],

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        'key' => env('GOOGLE_MAPS_API_KEY'), 
    ],
];
