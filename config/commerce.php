<?php

return [

    /**
     * Company information
     *
     * This will be shown on any receipts sent to customers.
     */

    'company' => [
        'name' => '',
        'address' => '',
        'city' => '',
        'country' => '',
        'zip_code' => '',
        'email' => ''
    ],

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
     * Currency
     *
     * Currently, Commerce only supports the use of one currency
     * for making purchases. Please choose which currency you want
     * to use. You can find a list of the currencies here.
     * https://github.com/damcclean/commerce/blob/master/database/seeds/CurrenciesTableSeeder.php
     */

    'currency' => 'USD',

    /**
     * Routes
     *
     * Commerce provides a set of web routes to make your store
     * function. You can change these routes if you have other
     * preferences.
     */

    'routes' => [

        /**
         * Cart
         *
         * - (index) Displays items in the customers' cart.
         * - (add) Adds an item to the customers' cart.
         * - (clear) Clears all items from the customers' cart.
         * - (delete) Removes an item from the customers' cart.
         */

        'cart' => [
            'index' => '/cart',
            'add' => '/cart',
            'clear' => '/cart/clear',
            'delete' => '/cart/delete',
        ],

        /**
         * Checkout
         *
         * - (show) Displays the checkout view to the user
         * - (store) Processes the users' order
         */

        'checkout' => [
            'show' => '/checkout',
            'store' => '/checkout',
        ],

        /**
         * Products
         *
         * - (index) Displays all products
         * - (search) Displays a product search to the user
         */

        'products' => [
            'index' => '/products',
            'search' => '/products/search',
        ],

        'thanks' => '/thanks', // Page user is redirected to once order has been processed.
        'redeem_coupon' => '/redeem-coupon', // Endpoint where we check if a coupon provided by the customer is valid
        'category' => '/category/category}', // Index page for a product category

    ],

    /**
     * How long should we keep your customers' cart in the
     * database for? (in days)
     */

    'cart-retention' => 30

];
