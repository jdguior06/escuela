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

    'pagofacil' => [
        'base_url' => env('PAGOFACIL_BASE_URL'),
        'token_service' => env('PAGOFACIL_TOKEN_SERVICE'),
        'token_secret' => env('PAGOFACIL_TOKEN_SECRET'),
        'client_code' => env('PAGOFACIL_CLIENT_CODE'),
        'callback_url' => env('PAGOFACIL_CALLBACK_URL'),
        'sandbox_divisor' => (float) env('PAGOFACIL_SANDBOX_DIVISOR', 1),
        'notification_phone' => env('PAGOFACIL_NOTIFICATION_PHONE'),
    ],

    'certificacion' => [
        'nota_minima' => (float) env('CERTIFICACION_NOTA_MINIMA', 70),
    ],

];
