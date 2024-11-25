<?php

use DuncanMcClean\SimpleCommerce\Cart\Calculator\ApplyCouponDiscounts;
use DuncanMcClean\SimpleCommerce\Cart\Calculator\ApplyShipping;
use DuncanMcClean\SimpleCommerce\Cart\Calculator\CalculateGrandTotal;
use DuncanMcClean\SimpleCommerce\Cart\Calculator\CalculateLineItems;
use DuncanMcClean\SimpleCommerce\Cart\Calculator\ResetTotals;

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

        'calculator_pipeline' => [
            ResetTotals::class,
            CalculateLineItems::class,
            ApplyCouponDiscounts::class,
            ApplyShipping::class,
            CalculateGrandTotal::class,
        ],
    ],

    'orders' => [
        'directory' => base_path('content/orders'),
    ],

    'coupons' => [
        'directory' => base_path('content/coupons'),
    ],

    'shipping' => [
        'default_method' => 'free_shipping',

        'methods' => ['free_shipping', 'dummy_shipping'],
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
