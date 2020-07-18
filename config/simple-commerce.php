<?php

return [
    'sites' => [
        'default' => [
            'currency' => 'GBP',

            'tax' => [
                'rate' => 20,
                'included_in_prices' => false,
            ],

            'shipping' => [
                'methods' => [
                    \DoubleThreeDigital\SimpleCommerce\Shipping\StandardPost::class,
                ],
            ],
        ],
    ],

    'gateways' => [
        \DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway::class => [],
    ],

    'collections' => [
        'products' => 'products',
        'orders' => 'orders',
        'coupons' => 'coupons',
        'product_categories' => 'product_categories',
        'order_statuses' => 'Order Statuses',
    ],

    'cart_key' => 'simple-commerce-cart',
];