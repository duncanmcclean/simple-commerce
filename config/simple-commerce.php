<?php

return [

    'sites' => [
        'default' => [
            'currency' => 'GBP',
        ],
    ],

    'products' => [
        'collections' => ['products'],
    ],

    'carts' => [
        'cookie_name' => 'simple-commerce-cart',

        'directory' => storage_path('statamic/simple-commerce/carts'),

        'unique_metadata' => false,

        'purge_abandoned_carts_after' => 90,

        // When a user logs in, and they've already started a cart elsewhere, should the two carts be merged?
        'merge_on_login' => true,
    ],

    'orders' => [
        'directory' => base_path('content/orders'),
    ],

    'shipping' => [
        'methods' => [
            \DuncanMcClean\SimpleCommerce\Shipping\FreeShipping::class => [],
        ],
    ],

    'payments' => [
        'gateways' => [
            \DuncanMcClean\SimpleCommerce\Payments\Gateways\DummyGateway::class => [
                'display' => 'Card',
            ],
        ],
    ],

    'notifications' => [
        'order_paid' => [
            // \DuncanMcClean\SimpleCommerce\Notifications\CustomerOrderPaid::class => [
            //     'to' => 'customer',
            // ],

            // \DuncanMcClean\SimpleCommerce\Notifications\BackOfficeOrderPaid::class => [
            //     'to' => 'duncan@example.com',
            // ],
        ],

        'order_dispatched' => [
            // \DuncanMcClean\SimpleCommerce\Notifications\CustomerOrderShipped::class => ['to' => 'customer'],
        ],
    ],

];
