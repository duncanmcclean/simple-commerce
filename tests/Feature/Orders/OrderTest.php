<?php

namespace Tests\Feature\Orders;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderTest extends TestCase
{
    #[Test]
    public function can_make_order_from_cart()
    {
        $cart = Cart::make()
            ->id('abc')
            ->lineItems([
                [
                    'product' => '123',
                    'quantity' => 1,
                    'total' => 2500,
                ],
            ])
            ->grandTotal(2500)
            ->subTotal(2500)
            ->set('foo', 'bar')
            ->set('baz', 'foobar');

        $order = Order::makeFromCart($cart);

        $this->assertInstanceOf(\DuncanMcClean\SimpleCommerce\Contracts\Orders\Order::class, $order);

        $this->assertEquals($cart->lineItems(), $order->lineItems());
        $this->assertEquals(2500, $order->grandTotal());
        $this->assertEquals(2500, $order->subTotal());
        $this->assertEquals(0, $order->discountTotal());
        $this->assertEquals(0, $order->taxTotal());
        $this->assertEquals(0, $order->shippingTotal());
        $this->assertEquals('bar', $order->get('foo'));
        $this->assertEquals('foobar', $order->get('baz'));
    }
}