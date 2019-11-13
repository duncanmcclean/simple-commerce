<?php

return [
    'company' => [
        'name' => 'Callister & Co Limited',
        'address' => '',
        'city' => '',
        'country' => '',
        'postal_code' => ''
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET')
    ],

    'currency' => env('COMMERCE_CURRENCY', 'gbp'),
    'user_required' => false,

    'notifications' => [
        'order_successful' => [
            'to' => 'shipping@example.com'
        ]
    ]
];
