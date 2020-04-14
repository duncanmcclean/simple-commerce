<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Helpers;

use DoubleThreeDigital\SimpleCommerce\Helpers\CartCalculator;
use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;
use DoubleThreeDigital\SimpleCommerce\Models\CartShipping;
use DoubleThreeDigital\SimpleCommerce\Models\CartTax;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class CartCalculatorTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function can_get_overall_total()
    {
        $cart = factory(Cart::class)->create();
        $items = factory(CartItem::class, 5)->create([
            'cart_id' => $cart->id,
        ]);
        $shipping = factory(CartShipping::class)->create([
            'cart_id' => $cart->id,
        ]);
        $tax = factory(CartTax::class)->create([
            'cart_id' => $cart->id,
        ]);

        $calculator = new CartCalculator($cart);
        $overallTotal = $calculator->calculate();

        $this->assertIsInt($overallTotal);
    }

    /** @test */
    public function can_get_items_total()
    {
        $cart = factory(Cart::class)->create();
        $items = factory(CartItem::class, 5)->create([
            'cart_id' => $cart->id,
        ]);

        $calculator = new CartCalculator($cart);
        $itemsTotal = $calculator->itemsTotal()->total;

        $this->assertIsInt($itemsTotal);
    }

    /** @test */
    public function can_get_shipping_total()
    {
        $cart = factory(Cart::class)->create();
        $items = factory(CartItem::class, 5)->create([
            'cart_id' => $cart->id,
        ]);
        $shipping = factory(CartShipping::class)->create([
            'cart_id' => $cart->id,
        ]);

        $calculator = new CartCalculator($cart);
        $itemsTotal = $calculator->itemsTotal();
        $shippingTotal = $calculator->shippingTotal()->total;

        $this->assertIsInt($shippingTotal);
    }

    /** @test */
    public function can_get_tax_total()
    {
        $cart = factory(Cart::class)->create();
        $items = factory(CartItem::class, 5)->create([
            'cart_id' => $cart->id,
        ]);
        $tax = factory(CartTax::class)->create([
            'cart_id' => $cart->id,
        ]);

        $calculator = new CartCalculator($cart);
        $itemsTotal = $calculator->itemsTotal();
        $taxTotal = $calculator->taxTotal()->total;

        $this->assertIsInt($taxTotal);
    }

    /** @test */
    public function can_get_tax_total_if_prices_are_entered_with_tax()
    {
        // TODO: write test
    }
}
