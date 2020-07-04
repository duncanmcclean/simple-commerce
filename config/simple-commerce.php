<?php

return [
    'sites' => [
        'default' => [
            'currency' => 'USD',
        ],
    ],

    'gateways' => [
        \DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway::class => [],
    ],
];