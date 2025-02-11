<?php

namespace Tests\Feature\Shipping;

use DuncanMcClean\SimpleCommerce\Facades;
use DuncanMcClean\SimpleCommerce\Shipping\ShippingOption;
use DuncanMcClean\SimpleCommerce\Taxes\TaxClass;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\ShippingMethods\FakeShippingMethod;
use Tests\TestCase;

class ShippingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        FakeShippingMethod::register();
    }

    #[Test]
    public function shipping_method_can_return_multiple_options()
    {
        $shippingMethod = Facades\ShippingMethod::find('fake_shipping_method');
        $options = $shippingMethod->options(Facades\Cart::make());

        $this->assertInstanceOf(Collection::class, $options);
        $this->assertCount(3, $options);

        $this->assertInstanceOf(ShippingOption::class, $options->first());
        $this->assertEquals('In-Store Pickup', $options->first()->name());
        $this->assertEquals('in-_store_pickup', $options->first()->handle());
        $this->assertEquals(0, $options->first()->price());
    }

    #[Test]
    public function shipping_option_can_be_augmented()
    {
        $shippingMethod = Facades\ShippingMethod::find('fake_shipping_method');
        $shippingOption = $shippingMethod->options(Facades\Cart::make())->firstWhere('handle', 'standard_shipping');

        $augmented = $shippingOption->toAugmentedArray();

        $this->assertEquals('Standard Shipping', $augmented['name']->value());
        $this->assertEquals('standard_shipping', $augmented['handle']->value());
        $this->assertEquals('£5.00', $augmented['price']->value());
        $this->assertEquals('fake_shipping_method', $augmented['shipping_method']->value());
    }

    #[Test]
    public function shipping_option_can_return_shipping_tax_class()
    {
        config()->set('statamic.simple-commerce.taxes.shipping_tax_behaviour', 'tax_class');

        $shippingMethod = Facades\ShippingMethod::find('fake_shipping_method');
        $shippingOption = $shippingMethod->options(Facades\Cart::make())->firstWhere('handle', 'standard_shipping');

        $taxClass = $shippingOption->purchasableTaxClass();

        $this->assertInstanceOf(TaxClass::class, $taxClass);
        $this->assertEquals('shipping', $taxClass->handle());
    }

    #[Test]
    public function shipping_option_can_return_highest_tax_rate()
    {
        $this->markTestIncomplete();
    }
}
