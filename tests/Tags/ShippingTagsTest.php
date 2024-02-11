<?php

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use DuncanMcClean\SimpleCommerce\Tags\ShippingTags;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\ShippingMethods\DPD;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\ShippingMethods\RoyalMail;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\ShippingMethods\StorePickup;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\StaticCartDriver;
use Statamic\Facades\Antlers;

uses(SetupCollections::class);
beforeEach(function () {
    $this->tag = resolve(ShippingTags::class)
        ->setParser(Antlers::parser())
        ->setContext([]);
});

test('can get available shipping method', function () {
    // This will add onto the existing one we have from the default config
    SimpleCommerce::registerShippingMethod('default', RoyalMail::class);
    SimpleCommerce::registerShippingMethod('default', DPD::class);

    $order = Order::make()
        ->merge([
            'shipping_name' => 'Santa',
            'shipping_address' => 'Christmas Lane',
            'shipping_city' => 'Snowcity',
            'shipping_country' => 'North Pole',
            'shipping_zip_code' => 'N0R P0L',
            'shipping_region' => null,
        ]);

    $order->save();

    StaticCartDriver::use()->setCart($order);

    $usage = $this->tag->methods();

    expect($usage)->toBeArray();
    expect($usage)->toHaveCount(2);

    expect('Free Shipping')->toBe($usage[0]['name']);
    expect('Royal Mail')->toBe($usage[1]['name']);
});

test('can get available shipping method when shipping method has config', function () {
    // This will add onto the existing one we have from the default config
    SimpleCommerce::registerShippingMethod('default', RoyalMail::class);
    SimpleCommerce::registerShippingMethod('default', DPD::class);
    SimpleCommerce::registerShippingMethod('default', StorePickup::class, [
        'location' => 'Glasgow',
    ]);

    $order = Order::make()
        ->merge([
            'shipping_name' => 'Santa',
            'shipping_address' => 'Christmas Lane',
            'shipping_city' => 'Snowcity',
            'shipping_country' => 'North Pole',
            'shipping_zip_code' => 'N0R P0L',
            'shipping_region' => null,
        ]);

    $order->save();

    StaticCartDriver::use()->setCart($order);

    $usage = $this->tag->methods();

    expect($usage)->toBeArray();
    expect($usage)->toHaveCount(3);

    expect('Free Shipping')->toBe($usage[0]['name']);
    expect('Royal Mail')->toBe($usage[1]['name']);
    expect('Store Pickup - Glasgow')->toBe($usage[2]['name']);
});
