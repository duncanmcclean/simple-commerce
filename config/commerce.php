<?php

return [

    /**
     * Company information
     *
     * This will be shown on any receipts sent to customers.
     */

    'company' => [
        'name' => 'Callister & Co Limited',
        'address' => '',
        'city' => '',
        'country' => '',
        'postal_code' => ''
    ],

    /**
     * Currency
     *
     * Commerce can only sell your products in a single currency.
     * By default, the currency used is Pound Sterling. You can
     * change it to any currency code supported by Stripe.
     * See: https://stripe.com/docs/currencies
     */

    'currency' => env('COMMERCE_CURRENCY', 'gbp'),

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
];
