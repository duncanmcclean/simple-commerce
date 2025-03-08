<?php

return [

    'products' => [
        'collections' => ['products'],

        'low_stock_threshold' => 5,
    ],

    'coupons' => [
        'directory' => base_path('content/coupons'),
    ],

    'routes' => [
        'checkout' => 'checkout',
        'checkout_confirmation' => 'checkout.confirmation',
    ],

    'carts' => [
        'repository' => 'file',

        // Flat file repository
        'directory' => base_path('content/simple-commerce/carts'),

        // Database repository
        'model' => \DuncanMcClean\SimpleCommerce\Cart\Eloquent\CartModel::class,
        'table' => 'carts',

        'line_items_model' => \DuncanMcClean\SimpleCommerce\Cart\Eloquent\LineItemModel::class,
        'line_items_table' => 'cart_line_items',

        'cookie_name' => 'simple-commerce-cart',

        'unique_metadata' => false,

        'purge_abandoned_carts_after' => 90,

        // When a user logs in, and they've already started a cart elsewhere, should the two carts be merged?
        'merge_on_login' => true,
    ],

    'orders' => [
        'repository' => 'file',

        // Flat file repository
        'directory' => base_path('content/simple-commerce/orders'),

        // Database repository
        'model' => \DuncanMcClean\SimpleCommerce\Orders\Eloquent\OrderModel::class,
        'table' => 'orders',

        'line_items_model' => \DuncanMcClean\SimpleCommerce\Orders\Eloquent\LineItemModel::class,
        'line_items_table' => 'order_line_items',
    ],

    'taxes' => [
        // Enable this when product prices are entered inclusive of tax.
        // When calculating taxes, the tax will be deducted from the product price, then added back on at the end.
        'price_includes_tax' => true,

        // Determines how tax is calculated on shipping costs. Options:
        // - highest_tax_rate: Charge the highest tax rate from the products in the cart.
        // - tax_class: When enabled, a new tax class will be created for shipping, allowing you to set a specific tax rate for shipping.
        'shipping_tax_behaviour' => 'tax_class',
    ],

    'payments' => [
        'gateways' => [
            'dummy' => [
                //
            ],

            'stripe' => [
                'key' => env('STRIPE_KEY'),
                'secret' => env('STRIPE_SECRET'),
                'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            ],

            //            'mollie' => [
            //                'api_key' => env('MOLLIE_KEY'),
            //                'profile_id' => env('MOLLIE_PROFILE_ID'),
            //            ],
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
