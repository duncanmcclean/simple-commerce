<?php

return [

    'products' => [
        'collections' => ['products'],
    ],

    'sites' => [
        'default' => [
            'currency' => 'GBP',

            'shipping' => [
                'methods' => [
                    \DuncanMcClean\SimpleCommerce\Shipping\FreeShipping::class => [],
                ],
            ],
        ],
    ],

    'payments' => [
        'gateways' => [
            \DuncanMcClean\SimpleCommerce\Payments\Gateways\DummyGateway::class => [
                'display' => 'Card',
            ],
        ],
    ],

    'notifications' => [
        'order_paid' => [
            // \DuncanMcClean\SimpleCommerce\Notifications\CustomerOrderPaid::class => [
            //     'to' => 'customer',
            // ],

            // \DuncanMcClean\SimpleCommerce\Notifications\BackOfficeOrderPaid::class => [
            //     'to' => 'duncan@example.com',
            // ],
        ],

        'order_dispatched' => [
            // \DuncanMcClean\SimpleCommerce\Notifications\CustomerOrderShipped::class => ['to' => 'customer'],
        ],
    ],

    'field_whitelist' => [
        'orders' => [
            // TODO: these default fields might need changing (in fact, this whitelist might end up being dynamic,
            // based on the fields in the order blueprint)
            'shipping_name', 'shipping_address', 'shipping_address_line1', 'shipping_address_line2', 'shipping_city',
            'shipping_region', 'shipping_postal_code', 'shipping_country', 'shipping_note', 'shipping_method',
            'use_shipping_address_for_billing', 'billing_name', 'billing_address', 'billing_address_line2',
            'billing_city', 'billing_region', 'billing_postal_code', 'billing_country',
        ],

        'line_items' => [],

        'customers' => ['name', 'email'],
    ],

];
