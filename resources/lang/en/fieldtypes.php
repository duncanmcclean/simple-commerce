<?php

return [
    'money' => [
        'title' => 'Money',

        'config_fields' => [
            'read_only' => 'Should this field be read only?',
        ],
    ],

    'product_variants' => [
        'title' => 'Product Variants',

        'config_fields' => [
            'option_fields' => [
                'display' => 'Option Fields',
                'instructions' => 'Configure fields that will be shown when an option is created.',
            ],
        ],
    ],
];
