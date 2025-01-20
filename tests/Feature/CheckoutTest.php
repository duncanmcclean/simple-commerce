<?php

namespace Tests\Feature;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;
use DuncanMcClean\SimpleCommerce\Coupons\CouponType;
use DuncanMcClean\SimpleCommerce\Events\CouponRedeemed;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Payments\Gateways\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;
use DuncanMcClean\SimpleCommerce\Facades;

class CheckoutTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Cart::forgetCurrentCart();

        FakePaymentGateway::register();
        config()->set('statamic.simple-commerce.payments.gateways', ['fake' => []]);
    }

    #[Test]
    public function can_checkout()
    {
        $cart = $this->makeCart();

        $this
            ->get('/!/simple-commerce/payments/fake/checkout')
            ->assertRedirect();

        $this->assertNotNull($order = Facades\Order::query()->where('cart', $cart->id())->first());
        $this->assertEquals(OrderStatus::PaymentReceived, $order->status());
    }

    #[Test]
    public function can_checkout_a_free_cart()
    {
        $cart = $this->makeCart();

        Entry::make()->collection('products')->id('product-2')->data(['price' => 0])->save();
        $cart->lineItems([['product' => 'product-2', 'total' => 0, 'quantity' => 1]])->save();

        $this
            ->get('/!/simple-commerce/cart/checkout')
            ->assertRedirect();

        $this->assertNotNull($order = Facades\Order::query()->where('cart', $cart->id())->first());
        $this->assertEquals(OrderStatus::PaymentReceived, $order->status());
        $this->assertEquals(0, $order->grandTotal());
    }

    #[Test]
    public function cant_checkout_with_invalid_payment_gateway()
    {
        $cart = $this->makeCart();

        $this
            ->get('/!/simple-commerce/payments/invalid/checkout')
            ->assertNotFound();

        $this->assertNull(Facades\Order::query()->where('cart', $cart->id())->first());
    }

    #[Test]
    public function cant_checkout_without_customer_information()
    {
        $cart = $this->makeCart();
        $cart->customer(null)->save();

        $this
            ->get('/!/simple-commerce/payments/fake/checkout')
            ->assertSessionHasErrors(['checkout' => 'Order cannot be created without customer information.']);

        $this->assertNull(Facades\Order::query()->where('cart', $cart->id())->first());
    }

    #[Test]
    public function cant_checkout_without_taxable_address()
    {
        $cart = $this->makeCart();
        $cart->remove('shipping_line_1')->save();

        $this
            ->get('/!/simple-commerce/payments/fake/checkout')
            ->assertSessionHasErrors(['checkout' => 'Order cannot be created without an address.']);

        $this->assertNull(Facades\Order::query()->where('cart', $cart->id())->first());
    }

    #[Test]
    public function cant_checkout_when_stock_is_unavilable()
    {
        $cart = $this->makeCart();
        $cart->lineItems()->first()->product()->set('stock', 0)->save();

        $this
            ->get('/!/simple-commerce/payments/fake/checkout')
            ->assertSessionHasErrors(['checkout' => 'One or more items in your cart are no longer available.']);

        $this->assertNull(Facades\Order::query()->where('cart', $cart->id())->first());
    }

    #[Test]
    public function cant_checkout_with_invalid_coupon()
    {
        $coupon = tap(Coupon::make()->code('foobar')->type(CouponType::Percentage)->amount(50)->set('expires_at', '2025-01-01'))->save();

        $cart = $this->makeCart();
        $cart->coupon($coupon)->saveWithoutRecalculating();

        $this
            ->get('/!/simple-commerce/payments/fake/checkout')
            ->assertSessionHasErrors(['checkout' => 'The coupon code is no longer valid for the items in your cart. Please remove it to continue.']);

        $this->assertNull(Facades\Order::query()->where('cart', $cart->id())->first());
    }

    #[Test]
    public function coupon_redeemed_event_is_dispatched()
    {
        Event::fake();

        $coupon = tap(Coupon::make()->code('foobar')->type(CouponType::Percentage)->amount(50))->save();

        $cart = $this->makeCart();
        $cart->coupon($coupon)->save();

        $this
            ->get('/!/simple-commerce/payments/fake/checkout')
            ->assertRedirect();

        $this->assertNotNull($order = Facades\Order::query()->where('cart', $cart->id())->first());
        $this->assertEquals(OrderStatus::PaymentReceived, $order->status());
        $this->assertEquals($coupon->id(), $order->coupon()->id());

        Event::assertDispatched(CouponRedeemed::class, fn ($event) => $event->coupon->id() === $coupon->id());
    }

    private function makeCart()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['price' => 5000])->save();

        $cart = Cart::make()
            ->customer(['name' => 'John Doe', 'email' => 'john.doe@example.com'])
            ->lineItems([['product' => 'product-1', 'total' => 5000, 'quantity' => 1]])
            ->merge([
                'shipping_line_1' => '123 Fake St',
                'shipping_city' => 'Fakeville',
                'shipping_postcode' => 'FA 1234',
                'shipping_country' => 'GBR',
                'shipping_state' => 'GLG',
            ]);

        $cart->save();

        Cart::setCurrent($cart);

        return $cart;
    }
}

class FakePaymentGateway extends PaymentGateway
{
    public static $handle = 'fake';

    public function setup(\DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart $cart): array
    {
        // TODO: Implement setup() method.

        return [];
    }

    public function process(Order $order): void
    {
        // Normally, this would be updated in the webhook, but for the sake of demonstration, we'll just update it here.
        $order->status(OrderStatus::PaymentReceived)->save();
    }

    public function capture(Order $order): void
    {
        // TODO: Implement capture() method.
    }

    public function cancel(\DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart $cart): void
    {
        // TODO: Implement cancel() method.
    }

    public function webhook(Request $request): Response
    {
        // TODO: Implement webhook() method.

        return response();
    }

    public function refund(Order $order, int $amount): void
    {
        // TODO: Implement refund() method.
    }
}
