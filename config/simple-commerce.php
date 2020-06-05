<?php

return [

    /**
     * Business Address.
     *
     * Address information for your business. By default,
     * this will be used as the location to set tax and
     * shipping prices.
     */
    'address' => [
        'address_1' => '',
        'address_2' => '',
        'address_3' => '',
        'city'      => '',
        'country'   => '',
        'state'     => '',
        'zip_code'  => '',
    ],

    /**
     * Payment Gateways.
     *
     * Simple Commerce gives you the ability to
     * configure different payment gateways.
     */
    'gateways' => [
        \DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway::class => [],
    ],

    /**
     * Currency.
     *
     * Control your currency settings. These will dictate
     * what currency products are sold in and how they are
     * formatted in the front-end.
     */
    'currency' => [
        'iso'       => 'USD',
        'position'  => 'left', // Options: left, right
        'separator' => '.',
    ],

    /**
     * Notifications.
     *
     * Configure what notifications we send and who we
     * send them to.
     */
    'notifications' => [
        'notifications' => [
            \DoubleThreeDigital\SimpleCommerce\Events\BackOffice\NewOrder::class               => ['mail'],
            \DoubleThreeDigital\SimpleCommerce\Events\BackOffice\VariantOutOfStock::class      => ['mail'],
            \DoubleThreeDigital\SimpleCommerce\Events\BackOffice\VariantStockRunningLow::class => ['mail'],
            \DoubleThreeDigital\SimpleCommerce\Events\OrderRefunded::class                     => ['mail'],
            \DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated::class                => ['mail'],
            \DoubleThreeDigital\SimpleCommerce\Events\OrderSuccessful::class                   => ['mail'],
        ],

        'mail' => [
            'to' => 'hello@example.com',

            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'name'    => env('MAIL_FROM_NAME', 'Example'),
            ],
        ],

        'slack' => [
            'webhook_url' => '',
        ],
    ],

    /**
     * Customers.
     *
     * Configure how you'd like to handle customers
     * in your store.
     */
    'customers' => [
        'model' => \App\User::class,
    ],

    /**
     * Other Settings.
     *
     * Some other settings for Simple Commerce.
     */
    'entered_with_tax'     => false,
    'calculate_tax_from'   => 'billingAddress', // Options: billingAddress, shippingAddress or businessAddress
    'shop_prices_with_tax' => true,
    'low_stock_counter'    => 5,
    'cart_session_key'     => 'simple_commerce_cart',
    'receipt_filesystem'   => 'public',

];
