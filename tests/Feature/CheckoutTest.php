<?php

namespace Tests\Feature;

use DuncanMcClean\SimpleCommerce\Coupons\CouponType;
use DuncanMcClean\SimpleCommerce\Events\CouponRedeemed;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Cart::forgetCurrentCart();
    }

    #[Test]
    public function cant_checkout_without_customer_information()
    {
        $this->makeCart();

        $this
            ->post('/!/simple-commerce/checkout')
            ->assertSessionHasErrors('customer');
    }

    #[Test]
    public function can_redeem_coupon()
    {
        Event::fake();

        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['price' => 5000])->save();

        $cart = tap($this->makeCart()
            ->customer(['name' => 'John Doe', 'email' => 'john.doe@example.com'])
            ->lineItems([['product' => 'product-1', 'total' => 5000, 'quantity' => 1]])
        )->save();

        $coupon = tap(Coupon::make()->code('FOOBAR')->type(CouponType::Percentage)->amount(25))->save();

        $this
            ->post('/!/simple-commerce/checkout', ['coupon' => 'FOOBAR'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertEquals($coupon->id(), $cart->fresh()->coupon()->id());
        $this->assertEquals($cart->fresh()->couponTotal(), 1250);

        Event::assertDispatched(CouponRedeemed::class);
    }

    #[Test]
    public function cant_redeem_coupon_when_code_is_invalid()
    {
        Event::fake();

        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['price' => 5000])->save();

        $cart = tap($this->makeCart()
            ->customer(['name' => 'John Doe', 'email' => 'john.doe@example.com'])
            ->lineItems([['product' => 'product-1', 'total' => 5000, 'quantity' => 1]])
        )->save();

        $this
            ->post('/!/simple-commerce/checkout', ['coupon' => 'FOOBAR'])
            ->assertSessionHasErrors('coupon');

        $this->assertNull($cart->fresh()->coupon());
        $this->assertEquals($cart->fresh()->couponTotal(), 0);

        Event::assertNotDispatched(CouponRedeemed::class);
    }

    #[Test]
    public function cant_redeem_coupon_when_invalid_for_card()
    {
        Event::fake();

        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['price' => 5000])->save();

        $cart = tap($this->makeCart()
            ->customer(['name' => 'John Doe', 'email' => 'john.doe@example.com'])
            ->lineItems([['product' => 'product-1', 'total' => 5000, 'quantity' => 1]])
        )->save();

        Coupon::make()->code('FOOBAR')->type(CouponType::Percentage)->amount(25)->set('products', ['product-2'])->save();

        $this
            ->post('/!/simple-commerce/checkout', ['coupon' => 'FOOBAR'])
            ->assertSessionHasErrors('coupon');

        $this->assertNull($cart->fresh()->coupon());
        $this->assertEquals($cart->fresh()->couponTotal(), 0);

        Event::assertNotDispatched(CouponRedeemed::class);
    }

    protected function makeCart()
    {
        $cart = tap(Cart::make())->save();

        Cart::setCurrent($cart);

        return $cart;
    }
}