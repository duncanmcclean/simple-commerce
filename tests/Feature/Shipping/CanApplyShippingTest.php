<?php

namespace Feature\Shipping;

use DuncanMcClean\SimpleCommerce\Cart\Calculator\ApplyShipping;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Shipping\ShippingMethod;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CanApplyShippingTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        PaidShipping::register();
    }

    #[Test]
    public function applies_shipping_cost_to_cart()
    {
        $cart = Cart::make()->set('shipping_method', 'paid_shipping');

        $cart = app(ApplyShipping::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals(500, $cart->shippingTotal());
    }

    #[Test]
    public function doesnt_apply_shipping_cost_when_cart_is_missing_a_shipping_method()
    {
        $cart = Cart::make();

        $cart = app(ApplyShipping::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals(0, $cart->shippingTotal());
    }

    #[Test]
    public function applies_shipping_cost_from_default_shipping_method_when_cart_is_missing_a_shipping_method()
    {
        config(['statamic.simple-commerce.shipping.default_method' => 'paid_shipping']);

        $cart = Cart::make();

        $cart = app(ApplyShipping::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals(500, $cart->shippingTotal());
    }
}

class PaidShipping extends ShippingMethod
{
    public function isAvailable(\DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart $cart): bool
    {
        return true;
    }

    function cost(\DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart $cart): int
    {
        return 500;
    }
}