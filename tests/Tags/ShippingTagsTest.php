<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\ShippingMethod;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Orders\Address;
use DoubleThreeDigital\SimpleCommerce\Shipping\BaseShippingMethod;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Tags\ShippingTags;
use DoubleThreeDigital\SimpleCommerce\Tests\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\StaticCartDriver;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Antlers;

class ShippingTagsTest extends TestCase
{
    use SetupCollections;

    protected $tag;

    public function setUp(): void
    {
        parent::setUp();

        $this->tag = resolve(ShippingTags::class)
            ->setParser(Antlers::parser())
            ->setContext([]);
    }

    /** @test */
    public function can_get_available_shipping_method()
    {
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

        $this->assertSame($usage[0]['name'], 'Standard Post');
        $this->assertSame($usage[1]['name'], 'Royal Mail');
    }

    /** @test */
    public function can_get_available_shipping_method_when_shipping_method_has_config()
    {
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

        $this->assertSame($usage[0]['name'], 'Standard Post');
        $this->assertSame($usage[1]['name'], 'Royal Mail');
        $this->assertSame($usage[2]['name'], 'Store Pickup - Glasgow');
    }
}

class RoyalMail extends BaseShippingMethod implements ShippingMethod
{
    public function name(): string
    {
        return 'Royal Mail';
    }

    public function description(): string
    {
        return 'Description of your shipping method';
    }

    public function calculateCost(OrderContract $order): int
    {
        return 0;
    }

    public function checkAvailability(OrderContract $order, Address $address): bool
    {
        return true;
    }
}

class DPD extends BaseShippingMethod implements ShippingMethod
{
    public function name(): string
    {
        return 'DPD';
    }

    public function description(): string
    {
        return 'Description of your shipping method';
    }

    public function calculateCost(OrderContract $order): int
    {
        return 0;
    }

    public function checkAvailability(OrderContract $order, Address $address): bool
    {
        return false;
    }
}

class StorePickup extends BaseShippingMethod implements ShippingMethod
{
    public function name(): string
    {
        return 'Store Pickup - ' . $this->config()->get('location');
    }

    public function description(): string
    {
        return 'Pick up your parcel from the store.';
    }

    public function calculateCost(OrderContract $order): int
    {
        return 0;
    }

    public function checkAvailability(OrderContract $order, Address $address): bool
    {
        return true;
    }
}
