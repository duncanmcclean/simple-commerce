<?php

namespace Feature\Cart;

use DuncanMcClean\SimpleCommerce\Cart\Calculator\CalculateGrandTotal;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CanCalculateGrandTotalTest extends TestCase
{
    #[Test]
    public function calculates_grand_total_correctly()
    {
        $cart = Cart::make()
            ->subTotal(5000)
            ->taxTotal(1000)
            ->discountTotal(500)
            ->shippingTotal(500);

        $cart = app(CalculateGrandTotal::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals(6000, $cart->grandTotal());
    }
}
