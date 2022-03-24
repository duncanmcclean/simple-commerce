<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Content Drivers
    |--------------------------------------------------------------------------
    |
    | Normally, all of your products, orders, coupons & customers are stored as flat
    | file entries. This works great for small stores where you want to keep everything
    | simple. However, for more complex stores, you may want store your data somewhere else
    | (like a database). Here's where you'd swap that out.
    |
    | https://simple-commerce.duncanmcclean.com/extending/content-drivers
    |
    */

    'content' => [
        'orders' => [
            'driver' => \DoubleThreeDigital\SimpleCommerce\Orders\Order::class,
            'collection' => 'orders',
        ],

        'products' => [
            'driver' => \DoubleThreeDigital\SimpleCommerce\Products\Product::class,
            'collection' => 'products',
        ],

        'coupons' => [
            'driver' => \DoubleThreeDigital\SimpleCommerce\Coupons\Coupon::class,
            'collection' => 'coupons',
        ],

        'customers' => [
            'driver' => \DoubleThreeDigital\SimpleCommerce\Customers\Customer::class, // Change to `UserCustomer` if you'd prefer to use Users as your customers
            'collection' => 'customers',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gateways
    |--------------------------------------------------------------------------
    |
    | You may configure as many payment gateways as you like. You can use one that's
    | built-in or a custom gateway you've built yourself.
    |
    | https://simple-commerce.duncanmcclean.com/gateways
    |
    */

    'gateways' => [
        \DoubleThreeDigital\SimpleCommerce\Gateways\Builtin\DummyGateway::class => [
            'display' => 'Card',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sites
    |--------------------------------------------------------------------------
    |
    | For each of your sites, you may configure a currency, tax rates and shipping
    | methods. This is useful for stores that sell the same products but in
    | different currencies/countries.
    |
    | https://simple-commerce.duncanmcclean.com/multi-site
    |
    */

    'sites' => [
        'default' => [
            'currency' => 'GBP',

            'shipping' => [
                'methods' => [
                    \DoubleThreeDigital\SimpleCommerce\Shipping\StandardPost::class,
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Simple Commerce can automatically send notifications after events occur in your store.
    | eg. a cart being completed.
    |
    | Here's where you can toggle if certain notifications are enabled/disabled.
    |
    | https://simple-commerce.duncanmcclean.com/notifications
    |
    */

    'notifications' => [
        'order_paid' => [
            \DoubleThreeDigital\SimpleCommerce\Notifications\CustomerOrderPaid::class   => ['to' => 'customer'],
            \DoubleThreeDigital\SimpleCommerce\Notifications\BackOfficeOrderPaid::class => ['to' => 'duncan@example.com'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cart
    |--------------------------------------------------------------------------
    |
    | Configure the Cart Driver in use on your site. It's what stores/gets the
    | Cart ID from the user's browser on every request.
    |
    */

    'cart' => [
        'driver' => \DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CookieDriver::class,
        'key'    => 'simple-commerce-cart',

        'unique_metadata' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Order Number
    |--------------------------------------------------------------------------
    |
    | If you want to, you can change the minimum order number for your store. This won't
    | affect past orders, just ones in the future.
    |
    */

    'minimum_order_number' => 2000,

    /*
    |--------------------------------------------------------------------------
    | Stock Running Low
    |--------------------------------------------------------------------------
    |
    | Simple Commerce can be configured to emit events when stock is running low for
    | products. Here is where you can configure the threshold when we start sending
    | those notifications.
    |
    */

    'low_stock_threshold' => 25,

    /*
    |--------------------------------------------------------------------------
    | Tax
    |--------------------------------------------------------------------------
    |
    | Configure the 'tax engine' you would like to use to calculate tax on
    | products & configure various tax-related settings.
    |
    */

    'tax_engine' => \DoubleThreeDigital\SimpleCommerce\Tax\Standard\TaxEngine::class,

    'tax_engine_config' => [
        // Basic Engine
        'rate'               => 20,
        'included_in_prices' => false,

        // Standard Tax Engine
        'address' => 'billing',

        'behaviour' => [
            'no_address_provided' => 'default_address',
            'no_rate_available' => 'prevent_checkout',
        ],

        'default_address' => [
            'address_line_1' => '',
            'address_line_2' => '',
            'city' => '',
            'region' => '',
            'country' => '',
            'zip_code' => '',
        ],
    ],

];
