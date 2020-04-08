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
        'country' => '',
        'state' => '',
        'zip_code' => '',
    ],

    /**
     * Payment Gateways
     *
     * Simple Commerce gives you the ability to
     * configure different payment gateways.
     */

    'gateways' => [
        \DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway::class => [],
    ],

    /**
     * Currency
     *
     * Control your currency settings. These will dictate
     * what currency products are sold in and how they are
     * formatted in the front-end.
     */

    'currency' => [
        'iso' => 'USD',
        'position' => 'left', // Options: left, right
        'separator' => '.',
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
     * Customers
     *
     * Configure how you'd like to handle customers
     * in your store.
     */

    'customers' => [
        'model' => \App\User::class,
    ],

    /**
     * Other Settings
     *
     * Some other settings for Simple Commerce.
     */

    'entered_with_tax' => false,
    'calculate_tax_from' => 'billingAddress', // Options: billingAddress, shippingAddress or businessAddress
    'shop_prices_with_tax' => true,
    'low_stock_counter' => 5,

];
