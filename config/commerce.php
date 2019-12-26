<?php

use Damcclean\Commerce\Stache\Repositories\FileCouponRepository;
use Damcclean\Commerce\Stache\Repositories\FileCustomerRepository;
use Damcclean\Commerce\Stache\Repositories\FileOrderRepository;
use Damcclean\Commerce\Stache\Repositories\FileProductRepository;

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
     * Currency
     *
     * Commerce can only sell your products in a single currency.
     * By default, the currency used is Pound Sterling. You can
     * change it to any currency code supported by Stripe.
     * See: https://stripe.com/docs/currencies
     */

    'currency' => [
        'code' => env('COMMERCE_CURRENCY', 'gbp'),
        'symbol' => env('COMMERCE_CURRENCY_SYMBOL', '£'),
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
         * - (add) Adds an item to the customers' cart.
         * - (clear) Clears all items from the customers' cart.
         * - (delete) Removes an item from the customers' cart.
         */

        'cart' => [
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
         * - (show) Displays a product page
         */

        'products' => [
            'index' => '/products',
            'search' => '/products/search',
            'show' => '/products/{product}',
        ],

        'thanks' => '/thanks', // Page user is redirected to once order has been processed.
    ],

    /**
     * Storage
     *
     * By default, Commerce stores your files in yaml files but if you
     * want to use a database, swap the repo out for an Eloquent one.
     */

    'storage' => [

        'coupons' => [
            'repository' => FileCouponRepository::class,
            'files' => base_path().'/content/commerce/coupons',
        ],

        'customers' => [
            'repository' => FileCustomerRepository::class,
            'files' => base_path().'/content/commerce/customers',
        ],

        'order' => [
            'repository' => FileOrderRepository::class,
            'files' => base_path().'/content/commerce/orders',
        ],

        'products' => [
            'repository' => FileProductRepository::class,
            'files' => base_path().'/content/commerce/products',
        ],

    ],
];
