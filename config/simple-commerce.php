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
