<?php

return [

    /**
     * Address
     *
     * This is where your business is located. Tax and shipping
     * prices will be generated from here. This address will also
     * appear on customers' receipts.
     */

    'address1' => '',
    'address2' => '',
    'address3' => '',
    'city' => '',
    'country' => '',
    'zip_code' => '',

    /**
     * Prices
     *
     * Set how your prices and currencies will be displayed
     * throughout Simple Commerce.
     */

    'currency' => 'USD',
    'currency_position' => 'left', // Options: 'left', 'right'
    'currency_separator' => '.',

    /**
     * Stripe
     *
     * We need these keys so your customers can purchase
     * products and so you can receive the money.
     *
     * You can find these keys here: https://dashboard.stripe.com/apikeys
     */

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET')
    ],

    /**
     * Routes
     *
     * Simple Commerce provides a set of web routes to make your store
     * function. You can change these routes if you have other
     * preferences.
     */

    'cart_index' => '/cart',
    'cart_store' => '/cart/add',
    'cart_clear' => '/cart/clear',
    'cart_remove' => '/cart/remove',
    'checkout_show' => '/checkout',
    'checkout_store' => '/checkout/store',
    'checkout_redirect' => '/thank-you',
    'product_index' => '/products',
    'product_search' => '/products/search',

    /**
     * How long should we keep your customers' cart in the
     * database for? (in days)
     */

    'cart-retention' => 30,

];
