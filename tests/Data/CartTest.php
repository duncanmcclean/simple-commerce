<?php

namespace Tests\Data;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\TaxClass;
use DuncanMcClean\SimpleCommerce\Facades\TaxZone;
use DuncanMcClean\SimpleCommerce\Shipping\ShippingOption;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\Fixtures\ShippingMethods\FakeShippingMethod;
use Tests\TestCase;

class CartTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function recalculates_totals_when_saving()
    {
        Collection::make('products')->save();
        Entry::make()->id('product-id')->collection('products')->data(['price' => 500])->save();

        // The totals here are wrong. They'll get fixed when the totals are recalculated.
        $cart = Cart::make()
            ->grandTotal(2000)
            ->subtotal(2000)
            ->discountTotal(500)
            ->lineItems([
                ['product' => 'product-id', 'quantity' => 2, 'total' => 2000],
            ]);

        $cart->save();

        $this->assertEquals(1000, $cart->grandTotal());
        $this->assertEquals(1000, $cart->subtotal());
        $this->assertEquals(0, $cart->discountTotal());
        $this->assertEquals(1000, $cart->lineItems()->first()->total());
    }

    #[Test]
    public function does_not_recalculate_totals_when_nothing_has_changed()
    {
        Collection::make('products')->save();
        Entry::make()->id('product-id')->collection('products')->data(['price' => 500])->save();

        $cart = Cart::make()
            ->grandTotal(1000)
            ->subtotal(1000)
            ->lineItems([
                ['product' => 'product-id', 'quantity' => 2, 'total' => 1000],
            ]);

        $cart->set('fingerprint', $fingerprint = $cart->fingerprint());

        $cart = \Mockery::mock($cart)->makePartial();
        $cart->shouldNotReceive('recalculate');

        $cart->save();

        $this->assertEquals(1000, $cart->grandTotal());
        $this->assertEquals(1000, $cart->subtotal());
        $this->assertEquals(0, $cart->discountTotal());
        $this->assertEquals(1000, $cart->lineItems()->first()->total());
        $this->assertEquals($fingerprint, $cart->get('fingerprint'));
    }

    #[Test]
    public function does_not_recalculate_totals_when_recalculating_is_disabled()
    {
        Collection::make('products')->save();
        Entry::make()->id('product-id')->collection('products')->data(['price' => 500])->save();

        // The totals here are wrong. They'll get fixed when the totals are recalculated.
        $cart = Cart::make()
            ->grandTotal(2000)
            ->subtotal(2000)
            ->discountTotal(500)
            ->lineItems([
                ['product' => 'product-id', 'quantity' => 2, 'total' => 2000],
            ]);

        $cart->saveWithoutRecalculating();

        $this->assertEquals(2000, $cart->grandTotal());
        $this->assertEquals(2000, $cart->subtotal());
        $this->assertEquals(500, $cart->discountTotal());
        $this->assertEquals(2000, $cart->lineItems()->first()->total());
    }

    #[Test]
    public function it_returns_the_tax_breakdown()
    {
        TaxClass::make()->handle('standard')->set('name', 'Standard')->save();
        TaxClass::make()->handle('reduced')->set('name', 'Reduced')->save();

        TaxZone::make()->handle('uk_vat')->data([
            'name' => 'UK VAT',
            'type' => 'countries',
            'countries' => ['GBR'],
            'rates' => ['standard' => 20, 'reduced' => 5],
        ])->save();

        TaxZone::make()->handle('gls_vat')->data([
            'name' => 'Glasgow VAT',
            'type' => 'states',
            'countries' => ['GBR'],
            'states' => ['GLS'],
            'rates' => ['reduced' => 4],
        ])->save();

        Collection::make('products')->save();

        $productA = Entry::make()->collection('products')->data(['price' => 10000, 'tax_class' => 'standard']);
        $productA->save();

        $productB = Entry::make()->collection('products')->data(['price' => 500, 'tax_class' => 'standard']);
        $productB->save();

        $productC = Entry::make()->collection('products')->data(['price' => 500, 'tax_class' => 'reduced']);
        $productC->save();

        $cart = Cart::make()
            ->lineItems([
                [
                    'id' => 'one',
                    'product' => $productA->id(),
                    'quantity' => 1,
                    'total' => 10000,
                    'tax_breakdown' => [
                        ['rate' => 20, 'description' => 'Standard', 'zone' => 'UK VAT', 'amount' => 2000],
                    ],
                ],
                [
                    'id' => 'two',
                    'product' => $productB->id(),
                    'quantity' => 1,
                    'total' => 500,
                    'tax_breakdown' => [
                        ['rate' => 20, 'description' => 'Standard', 'zone' => 'UK VAT', 'amount' => 100],
                    ],
                ],
                [
                    'id' => 'three',
                    'product' => $productC->id(),
                    'quantity' => 1,
                    'total' => 500,
                    'tax_breakdown' => [
                        ['rate' => 5, 'description' => 'Reduced', 'zone' => 'UK VAT', 'amount' => 25],
                        ['rate' => 4, 'description' => 'Reduced', 'zone' => 'Glasgow VAT', 'amount' => 20],
                    ],
                ],
            ])
            ->subtotal(10500)
            ->shippingTotal(2000)
            ->data([
                'shipping_line_1' => '123 Fake St',
                'shipping_city' => 'Glasgow',
                'shipping_postcode' => 'G1 234',
                'shipping_country' => 'GBR',
                'shipping_state' => 'GLS',
                'shipping_method' => 'paid_shipping',
                'shipping_option' => 'the_only_option',
                'shipping_tax_breakdown' => [
                    ['rate' => 20, 'description' => 'Standard', 'zone' => 'UK VAT', 'amount' => 400],
                ],
            ]);

        $this->assertEquals([
            ['rate' => 20, 'description' => 'Standard', 'zone' => 'UK VAT', 'amount' => 2500],
            ['rate' => 5, 'description' => 'Reduced', 'zone' => 'UK VAT', 'amount' => 25],
            ['rate' => 4, 'description' => 'Reduced', 'zone' => 'Glasgow VAT', 'amount' => 20],
        ], $cart->taxBreakdown());
    }

    #[Test]
    public function it_returns_the_shipping_method()
    {
        FakeShippingMethod::register();

        $cart = Cart::make()->set('shipping_method', 'fake_shipping_method');

        $this->assertInstanceOf(FakeShippingMethod::class, $cart->shippingMethod());
    }

    #[Test]
    public function it_returns_the_shipping_option()
    {
        FakeShippingMethod::register();

        $cart = Cart::make()
            ->set('shipping_method', 'fake_shipping_method')
            ->set('shipping_option', [
                'name' => 'Standard Shipping',
                'handle' => 'standard_shipping',
                'price' => 500,
            ]);

        $this->assertInstanceOf(ShippingOption::class, $cart->shippingOption());
        $this->assertEquals('Standard Shipping', $cart->shippingOption()->name());
        $this->assertEquals(500, $cart->shippingOption()->price());
    }
}
