<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\ShippingMethod;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Orders\Address;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Tags\ShippingTags;
use DoubleThreeDigital\SimpleCommerce\Tests\StaticCartDriver;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Antlers;

class ShippingTagsTest extends TestCase
{
    protected $tag;

    public function setUp(): void
    {
        parent::setUp();

        $this->tag = resolve(ShippingTags::class)
            ->setParser(Antlers::parser())
            ->setContext([]);
    }

    /**
     * @test
     */
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
}

class RoyalMail implements ShippingMethod
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

class DPD implements ShippingMethod
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
