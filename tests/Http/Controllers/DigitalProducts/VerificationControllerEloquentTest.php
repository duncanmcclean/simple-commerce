<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use DoubleThreeDigital\SimpleCommerce\Facades\LicenseKey;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\UseDatabaseContentDrivers;
use Statamic\Facades\Collection;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderModel;

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
