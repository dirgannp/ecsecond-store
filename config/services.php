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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'github' => [
        'client_id' => 'YOUR_GITHUB_API', //Github API
        'client_secret' => 'YOUR_GITHUB_SECRET', //Github Secret
        'redirect' => 'http://localhost:8000/login/github/callback',
     ],
     'google' => [
        'client_id' => 'YOUR_GOOGLE_API', //Google API
        'client_secret' => 'YOUR_GOOGLE_SECRET', //Google Secret
        'redirect' => 'http://localhost:8000/login/google/callback',
     ],
     'facebook' => [
        'client_id' => 'YOUR_FACEBOOK_API', //Facebook API
        'client_secret' => 'YOUR_FACEBOK_SECRET', //Facebook Secret
        'redirect' => 'http://localhost:8000/login/facebook/callback',
     ],
     'rajaongkir' => [
        'base_url' => env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1'),
        'api_key' => env('RAJAONGKIR_API_KEY'),
        'origin_id' => env('RAJAONGKIR_ORIGIN_ID'),
        'couriers' => env('RAJAONGKIR_COURIERS', 'lion'),
        'default_weight' => env('RAJAONGKIR_DEFAULT_WEIGHT', 1000),
     ],
     'komerce' => [
         'qris_base_url' => env('KOMERCE_QRIS_BASE_URL', 'https://api-sandbox.collaborator.komerce.id/user'),
         'qris_api_key' => env('KOMERCE_QRIS_API_KEY'),
         'qris_id' => env('KOMERCE_QRIS_ID'),
         'qris_unique_amount' => env('KOMERCE_QRIS_UNIQUE_AMOUNT', true),
      ],
     'paymentku' => [
         'base_url' => env('PAYMENTKU_BASE_URL', 'https://api.paymentku.com'),
         'api_key' => env('PAYMENTKU_API_KEY'),
         'merchant_id' => env('PAYMENTKU_MERCHANT_ID'),
         'qris_type' => env('PAYMENTKU_QRIS_TYPE', 'QRIS'),
         'callback_url' => env('PAYMENTKU_CALLBACK_URL'),
      ],

];
