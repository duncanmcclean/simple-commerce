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
    */

    'gateways' => [
        \DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway::class => [],
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
    ],

    /*
    |--------------------------------------------------------------------------
    | Cart Key
    |--------------------------------------------------------------------------
    |
    | Under the hood, Simple Commerce sets an entry in the session to store the customers'
    | current cart ID. If you want to, you can change the key of the session entry.
    |
    */

    'cart_key' => 'simple-commerce-cart',

];
