<?php

namespace Tests\Feature\Cart;

use DuncanMcClean\SimpleCommerce\Cart\Calculator\CalculateTotals;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CanCalculateTotalsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
        Entry::make()->id('product-id')->collection('products')->data(['price' => 500])->save();
    }

    #[Test]
    public function calculates_grand_total_correctly_when_prices_include_tax()
    {
        config()->set('statamic.simple-commerce.taxes.price_includes_tax', true);

        $cart = Cart::make()
            ->lineItems([
                [
                    'product' => 'product-id',
                    'quantity' => 1,
                    'unit_price' => 500,
                    'sub_total' => 500,
                    'tax_total' => 20,
                    'total' => 500,
                ],
            ])
            ->subTotal(500)
            ->shippingTotal(500)
            ->set('shipping_tax_total', 20)
            ->taxTotal(40);

        $cart = app(CalculateTotals::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals(1000, $cart->grandTotal());
    }

    #[Test]
    public function calculates_grand_total_correctly_when_prices_exclude_tax()
    {
        config()->set('statamic.simple-commerce.taxes.price_includes_tax', false);

        $cart = Cart::make()
            ->lineItems([
                [
                    'product' => 'product-id',
                    'quantity' => 1,
                    'unit_price' => 500,
                    'sub_total' => 500,
                    'tax_total' => 20,
                    'total' => 520,
                ],
            ])
            ->subTotal(500)
            ->shippingTotal(520)
            ->set('shipping_tax_total', 20)
            ->taxTotal(40);

        $cart = app(CalculateTotals::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals(1040, $cart->grandTotal());
    }

    #[Test]
    public function discount_total_is_subtracted_from_grand_total()
    {
        config()->set('statamic.simple-commerce.taxes.price_includes_tax', true);

        $cart = Cart::make()
            ->lineItems([
                [
                    'product' => 'product-id',
                    'quantity' => 1,
                    'unit_price' => 500,
                    'sub_total' => 500,
                    'tax_total' => 20,
                    'total' => 500,
                ],
            ])
            ->subTotal(500)
            ->shippingTotal(500)
            ->set('shipping_tax_total', 20)
            ->taxTotal(40)
            ->discountTotal(400);

        $cart = app(CalculateTotals::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals(600, $cart->grandTotal());
    }
}
