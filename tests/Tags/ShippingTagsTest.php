<?php

use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\ShippingMethod;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Orders\Address;
use DoubleThreeDigital\SimpleCommerce\Shipping\BaseShippingMethod;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Tags\ShippingTags;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\StaticCartDriver;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Statamic\Facades\Antlers;

uses(TestCase::class);
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

    $this->assertIsArray($usage);
    $this->assertCount(2, $usage);

    $this->assertSame($usage[0]['name'], 'Free Shipping');
    $this->assertSame($usage[1]['name'], 'Royal Mail');
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

    $this->assertIsArray($usage);
    $this->assertCount(3, $usage);

    $this->assertSame($usage[0]['name'], 'Free Shipping');
    $this->assertSame($usage[1]['name'], 'Royal Mail');
    $this->assertSame($usage[2]['name'], 'Store Pickup - Glasgow');
});

// Helpers
function name(): string
{
    return 'Store Pickup - '.test()->config()->get('location');
}

function description(): string
{
    return 'Pick up your parcel from the store.';
}

function calculateCost(OrderContract $order): int
{
    return 0;
}

function checkAvailability(OrderContract $order, Address $address): bool
{
    return true;
}
