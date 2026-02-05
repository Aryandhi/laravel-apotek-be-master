<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Xendit Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Xendit payment gateway integration.
    | Set XENDIT_ENABLED=true to enable Xendit payments.
    |
    */

    'enabled' => env('XENDIT_ENABLED', false),

    'secret_key' => env('XENDIT_SECRET_KEY'),

    'public_key' => env('XENDIT_PUBLIC_KEY'),

    'webhook_token' => env('XENDIT_WEBHOOK_TOKEN'),

    'is_production' => env('XENDIT_IS_PRODUCTION', false),

    /*
    |--------------------------------------------------------------------------
    | Invoice Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for Xendit invoices.
    |
    */

    'invoice' => [
        // Invoice duration in seconds (1 hour = 3600 seconds)
        'duration' => env('XENDIT_INVOICE_DURATION', 3600),

        // Currency for transactions
        'currency' => env('XENDIT_CURRENCY', 'IDR'),

        // Success redirect URL after payment
        'success_redirect_url' => env('XENDIT_SUCCESS_REDIRECT_URL'),

        // Failure redirect URL after payment
        'failure_redirect_url' => env('XENDIT_FAILURE_REDIRECT_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    |
    | Available payment methods to enable.
    |
    */

    'payment_methods' => [
        'ewallet' => [
            'enabled' => true,
            'channels' => ['OVO', 'DANA', 'SHOPEEPAY', 'LINKAJA', 'ASTRAPAY'],
        ],
        'qris' => [
            'enabled' => true,
        ],
        'virtual_account' => [
            'enabled' => true,
            'channels' => ['BCA', 'BNI', 'BRI', 'MANDIRI', 'PERMATA', 'BSI'],
        ],
    ],
];
