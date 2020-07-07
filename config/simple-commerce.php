<?php

return [
    'sites' => [
        'default' => [
            'currency' => 'GBP',

            'tax' => [
                'rate' => 20,
                'included_in_prices' => false,
            ],

            'shipping' => [
                'methods' => [
                    \DoubleThreeDigital\SimpleCommerce\Shipping\StandardPost::class,
                ],
            ],
        ],
    ],

    'gateways' => [
        \DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway::class => [],
    ],
];