<?php

use DuncanMcClean\SimpleCommerce\Facades\LicenseKey;
use DuncanMcClean\SimpleCommerce\Orders\OrderModel;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\UseDatabaseContentDrivers;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Statamic\Facades\Collection;

uses(RefreshDatabase::class);
uses(UseDatabaseContentDrivers::class);

it('can get verification index', function () {
    $licenseKey = LicenseKey::generate();

    Collection::make('orders')->save();

    OrderModel::create([
        'order_status' => 'placed',
        'payment_status' => 'paid',
        'items' => [
            [
                'metadata' => [
                    'license_key' => $licenseKey,
                ],
            ],
        ],
    ]);

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
