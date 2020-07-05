<?php

return [
    'sites' => [
        'default' => [
            'currency' => 'GBP',

            'tax' => [
                'rate' => 20,
                'included_in_prices' => false,
            ],
        ],
    ],

    'gateways' => [
        \DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway::class => [],
    ],
];