<?php

use DuncanMcClean\SimpleCommerce\Facades\LicenseKey;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;

it('can get verification index', function () {
    $licenseKey = LicenseKey::generate();

    Collection::make('orders')->save();

    Entry::make()
        ->collection('orders')
        ->set('order_status', 'placed')
        ->set('payment_status', 'paid')
        ->set('items', [
            [
                'metadata' => [
                    'license_key' => $licenseKey,
                ],
            ],
        ])
        ->save();

    $this
        ->post('/!/simple-commerce/digital-products/verification', [
            'license_key' => $licenseKey,
        ])
        ->assertOk()
        ->assertJson([
            'license_key' => $licenseKey,
            'valid' => true,
        ]);
});
