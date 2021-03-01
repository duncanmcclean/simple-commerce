<?php

return [
    'gateway_does_not_exist' => 'The provided gateway (:gateway) does not exist.',
    'no_gateway_provided'    => 'No gateway provided, can not checkout without a gateway.',

    'dummy' => [],

    'stripe' => [
        'no_payment_intent_provided' => 'No payment intent has been provided, a refund is not possible without a payment intent.',
        'stripe_secret_missing'      => 'Your Stripe secret couldn\'t be found. Make sure to add it to your gateway configuration.',
    ],

    'mollie' => [],
];
