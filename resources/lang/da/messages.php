<?php

return [

    'actions' => [
        'mark_as_paid' => 'Markér som betalt',
        'mark_as_shipped' => 'Markér som afsendt',
        'refund' => 'Refundere',
    ],

    'fieldtypes' => [
        'money' => [
            'title' => 'Penge',

            'config_fields' => [
                'read_only' => 'Skal dette felt kun læses?',
            ],
        ],

        'product_variants' => [
            'title' => 'Produktvarianter',

            'config_fields' => [
                'option_fields' => [
                    'display'      => 'Indstillingsfelter',
                    'instructions' => 'Konfigurer felter, der vises, når en indstilling oprettes.',
                ],
            ],
        ],
    ],

    'gateways' => [
        'stripe' => [
            'no_payment_intent_provided' => 'Der er ikke angivet nogen betalingshensigt, en tilbagebetaling er ikke mulig uden en betalingshensigt.',
            'stripe_secret_missing'      => 'Din Stripe secret-key kunne ikke findes. Sørg for at tilføje det til din gateway-konfiguration.',
        ],
    ],

    'shipping_methods' => [
        'standard_post' => [
            'name'        => 'Standard Post',
            'description' => 'Sendt gennem det nationale postvæsen. Leveres normalt indenfor 1-2 hverdage.',
        ],
    ],

    'validation' => [
        'product_exists' => 'Produktet :value eksisterer ikke.',
        'coupon_exists' => 'Kuponen :value findes ikke.',
        'country_exists' => 'Det valgte land genkendes ikke.',
        'is_a_gateway' => ':value er ikke en gateway',
        'region_exists' => 'Den valgte region genkendes ikke.',
        'tax_category_exists' => 'Beklager, den angivne skattekategori kunne ikke findes.',
        'tax_zone_exists' => 'Beklager, den angivne skattezone blev ikke fundet.',
        'valid_coupon' => 'Beklager, denne kupon er ikke gyldig til din ordre.',
        'email_address_contains_spaces' => 'Din e-mail må ikke indeholde mellemrum.',
    ],

    'customer_title'            => ':name <:email>',
    'product_has_no_variants'   => 'Ingen varianter.',
    'product_variants_singular' => 'variant',
    'product_variants_plural'   => 'varianter',

    'cart_updated'             => 'Kurv opdateret',
    'cart_deleted'             => 'Kurv slettet',
    'cart_item_added'          => 'Tilføjet til kurv',
    'cart_item_updated'        => 'Kurv vare opdateret',
    'cart_item_deleted'        => 'Varen er fjernet fra kurven',
    'checkout_complete'        => 'Checkout udført!',
    'customer_updated'         => 'Kunde opdateret',
    'coupon_added_to_cart'     => 'Kupon tilføjet til kurven',
    'coupon_removed_from_cart' => 'Kupon fjernet fra kurven',
    'invalid_coupon'           => 'Kuponen er ikke gyldig.',
];
