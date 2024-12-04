<?php

use DuncanMcClean\SimpleCommerce\Cart\Calculator;

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
            Calculator\ResetTotals::class,
            Calculator\CalculateLineItems::class,
            Calculator\ApplyCouponDiscounts::class,
            Calculator\ApplyShipping::class,
            Calculator\CalculateGrandTotal::class,
        ],
    ],

    'taxes' => [
        // Enable this when product prices are entered inclusive of tax.
        // When calculating taxes, the tax will be deducted from the product price, then added back on at the end.
        'price_includes_tax' => true,

        // Determines how tax is calculated on shipping costs. Options:
        // - highest_tax_rate: Charge the highest tax rate from the products in the cart.
        // - tax_class: When enabled, a new tax class will be created for shipping, allowing you to set a specific tax rate for shipping.
        'shipping_tax_behaviour' => 'none',
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
