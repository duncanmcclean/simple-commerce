<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sites
    |--------------------------------------------------------------------------
    |
    | For each of your Statamic sites, you can setup a new store which allows you
    | to use different currencies, tax rates and shipping methods.
    |
    */

    'sites' => [
        'default' => [
            'currency' => 'GBP',

            'tax' => [
                'rate'               => 20,
                'included_in_prices' => false,
            ],

            'shipping' => [
                'methods' => [
                    \DoubleThreeDigital\SimpleCommerce\Shipping\StandardPost::class,
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gateways
    |--------------------------------------------------------------------------
    |
    | You can setup multiple payment gateways for your store with Simple Commerce.
    | Here's where you can configure the gateways in use.
    |
    | https://sc-docs.doublethree.digital/v2.1/gateways
    |
    */

    'gateways' => [
        \DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway::class => [
            'display' => 'Card',
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
    | https://sc-docs.doublethree.digital/v2.1/email
    |
    */

    'notifications' => [
        'customer' => [
            'order_confirmation' => true,
        ],

        'back_office' => [
            'to' => 'staff@example.com',

            'order_paid' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Collections & Taxonomies
    |--------------------------------------------------------------------------
    |
    | Simple Commerce uses Statamic's native collections and taxonomies functionality.
    | It will automatically create collections/taxonomies upon addon installation if
    | they don't already exist. However, if you'd like to use a different collection
    | or taxonomy, like one you've already setup, here's the place to change that.
    |
    */

    'collections' => [
        'products'  => 'products',
        'orders'    => 'orders',
        'coupons'   => 'coupons',
        'customers' => 'customers',
    ],

    'taxonomies' => [
        'product_categories' => 'product_categories',
        'order_statuses'     => 'order_statuses',
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
        'driver' => \DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\SessionDriver::class,
        'key' => 'simple-commerce-cart',
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

];
