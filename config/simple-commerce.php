<?php

return [

    'products' => [
        'collections' => ['products'],

        'low_stock_threshold' => 5,
    ],

    'carts' => [
        'cookie_name' => 'simple-commerce-cart',

        'directory' => storage_path('statamic/simple-commerce/carts'),

        'unique_metadata' => false,

        'purge_abandoned_carts_after' => 90,

        // When a user logs in, and they've already started a cart elsewhere, should the two carts be merged?
        'merge_on_login' => true,
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

    'orders' => [
        'repository' => 'file',

        // Flat file repository
        'directory' => base_path('content/orders'),

        // Database repository
        'model' => \DuncanMcClean\SimpleCommerce\Orders\Eloquent\OrderModel::class,
        'table' => 'orders',

        'line_items_model' => \DuncanMcClean\SimpleCommerce\Orders\Eloquent\LineItemModel::class,
        'line_items_table' => 'line_items',
    ],

    'routes' => [
        'checkout' => 'checkout',
        'checkout_confirmation' => 'checkout.confirmation',
    ],

    'coupons' => [
        'directory' => base_path('content/coupons'),
    ],

    'payments' => [
        'gateways' => [
            'dummy' => [
                //
            ],
            //
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
