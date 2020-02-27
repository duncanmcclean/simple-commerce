<?php

return [

    /**
     * Business Address
     *
     * Address information for your business. By default,
     * this will be used as the location to set tax and
     * shipping prices.
     */

    'address' => [
        'address_1' => '',
        'address_2' => '',
        'address_3' => '',
        'city' => '',
        'country' => DoubleThreeDigital\SimpleCommerce\Models\Country::where('iso', 'UK'),
        'state' => '',
        'zip_code' => '',
    ],

    /**
     * Payment Gateways
     *
     * Simple Commerce uses Omnipay to interface with
     * different payment gateways. You can configure which
     * ones you want to use for your store here.
     */

    'gateways' => [
        'Stripe' => [
            'key' => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET'),
        ],
    ],

    /**
     * Currency
     *
     * Control your currency settings. These will dictate
     * what currency products are sold in and how they are
     * formatted in the front-end.
     */

    'currency' => 'USD',
    'currency_position' => 'left', // Options: 'left', 'right'
    'currency_separator' => '.',

    /**
     * Routes
     *
     * Simple Commerce provides a set of web routes to make your store
     * function. You can change these routes if you have other
     * preferences.
     */

    'routes' => [
        'cart_index' => '/cart',
        'cart_store' => '/cart/add',
        'cart_clear' => '/cart/clear',
        'cart_remove' => '/cart/remove',
        'checkout_show' => '/checkout',
        'checkout_store' => '/checkout/store',
        'checkout_redirect' => '/thank-you',
        'product_index' => '/products',
        'product_search' => '/products/search',
        'product_show' => '/products/{product}',
        'categories_show' => '/category/{category}',
    ],

    /**
     * Notifications
     *
     * Configure where we send your store's back
     * office notifications.
     */

    'notifications' => [
        'channel' => ['mail'],

        'mail_to' => 'admin@example.com',
        'slack_webhook' => '',
    ],

    /**
     * Tax & Shipping
     *
     * Configure the tax and shipping settings
     * for your store.
     */

    'entered_with_tax' => false,
    'calculate_tax_from' => 'billingAddress', // Options: billingAddress, shippingAddress or businessAddress
    'shop_prices_with_tax' => true,
    'cart_retention' => 30,

];
