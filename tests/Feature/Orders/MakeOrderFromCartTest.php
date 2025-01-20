<?php

namespace Tests\Feature\Orders;

use DuncanMcClean\SimpleCommerce\Coupons\CouponType;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class MakeOrderFromCartTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
    }

    #[Test]
    public function it_can_make_an_order_from_a_cart()
    {
        $product = tap(Entry::make()->collection('products')->set('price', 2500))->save();
        $coupon = tap(Coupon::make()->code('foobar')->type(CouponType::Percentage)->amount(50))->save();

        $cart = Cart::make()
            ->id('123')
            ->customer([
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
            ])
            ->coupon($coupon->id())
            ->lineItems([
                ['id' => '1', 'product' => $product->id(), 'quantity' => 2, 'unit_price' => 1000, 'total' => 2000],
            ])
            ->grandTotal(2500)
            ->subTotal(2000)
            ->discountTotal(100)
            ->taxTotal(400)
            ->shippingTotal(200)
            ->data([
                'shipping_method' => 'free_shipping',
                'shipping_option' => 'free_shipping',
                'shuffling' => 'is fun',
            ]);

        $order = Order::makeFromCart($cart);

        $this->assertEquals([
            'cart' => '123',
            'customer' => [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
            ],
            'coupon' => $coupon->id(),
            'line_items' => [
                ['id' => '1', 'product' => $product->id(), 'quantity' => 2, 'unit_price' => 1000, 'total' => 2000],
            ],
            'shipping_method' => 'free_shipping',
            'grand_total' => 2500,
            'sub_total' => 2000,
            'discount_total' => 100,
            'tax_total' => 400,
            'shipping_total' => 200,
            'shipping_option' => 'free_shipping',
            'shuffling' => 'is fun',
            'status' => 'payment_pending',
        ], $order->fileData());
    }
}
