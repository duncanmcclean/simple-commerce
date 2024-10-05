<?php

namespace Tests\Feature\Coupons;

use DuncanMcClean\SimpleCommerce\Coupons\CouponType;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Orders\Calculator\ApplyCouponDiscounts;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Tests\TestCase;

class CanApplyCouponDiscountsTest extends TestCase
{
    /** @test */
    public function applies_percentage_discount()
    {
        $this->makeProduct('123')->set('price', 2500)->save();

        $coupon = tap(Coupon::make()->code('foobar')->type(CouponType::Percentage)->amount(50))->save();

        $cart = Cart::make()->coupon($coupon->id())->lineItems([
            ['id' => 'abc', 'product' => '123', 'quantity' => 1, 'total' => 2500],
        ]);

        $cart = app(ApplyCouponDiscounts::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals($cart->couponTotal(), 1250);
        $this->assertEquals($cart->lineItems()->find('abc')->get('discount_amount'), 1250);
    }

    /** @test */
    public function applies_fixed_discount()
    {
        $this->makeProduct('123')->set('price', 2500)->save();

        $coupon = tap(Coupon::make()->code('foobar')->type(CouponType::Fixed)->amount(450))->save();

        $cart = Cart::make()->coupon($coupon->id())->lineItems([
            ['id' => 'abc', 'product' => '123', 'quantity' => 1, 'total' => 2500],
        ]);

        $cart = app(ApplyCouponDiscounts::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals($cart->couponTotal(), 450);
        $this->assertEquals($cart->lineItems()->find('abc')->get('discount_amount'), 450);
    }

    /** @test */
    public function only_applies_discount_to_valid_line_items()
    {
        $this->makeProduct('123')->set('price', 2500)->save();
        $this->makeProduct('456')->set('price', 5000)->save();

        $coupon = tap(Coupon::make()->code('foobar')->type(CouponType::Percentage)->amount(50)->set('products', ['123', '789']))->save();

        $cart = Cart::make()->coupon($coupon->id())->lineItems([
            ['id' => 'abc', 'product' => '123', 'quantity' => 1, 'total' => 2500],
            ['id' => 'def', 'product' => '456', 'quantity' => 1, 'total' => 5000],
        ]);

        $cart = app(ApplyCouponDiscounts::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals($cart->couponTotal(), 1250);
        $this->assertEquals($cart->lineItems()->find('abc')->get('discount_amount'), 1250);
        $this->assertNull($cart->lineItems()->find('def')->get('discount_amount'));
    }

    /** @test */
    public function discount_is_removed_from_line_items_when_no_longer_eligible()
    {
        $this->makeProduct('123')->set('price', 2500)->save();
        $this->makeProduct('456')->set('price', 5000)->save();

        $coupon = tap(Coupon::make()->code('foobar')->type(CouponType::Percentage)->amount(50)->set('products', ['123', '789']))->save();

        $cart = Cart::make()->coupon($coupon->id())->lineItems([
            ['id' => 'abc', 'product' => '123', 'quantity' => 1, 'total' => 2500, 'discount_amount' => 1250],
            ['id' => 'def', 'product' => '456', 'quantity' => 1, 'total' => 5000, 'discount_amount' => 2500], // This product is no longer eligible.
        ]);

        $cart = app(ApplyCouponDiscounts::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals($cart->couponTotal(), 1250);
        $this->assertEquals($cart->lineItems()->find('abc')->get('discount_amount'), 1250);
        $this->assertNull($cart->lineItems()->find('def')->get('discount_amount'));
    }

    /** @test */
    public function coupon_is_removed_when_there_is_no_discount()
    {
        $this->makeProduct('123')->set('price', 2500)->save();

        $coupon = tap(Coupon::make()->code('foobar')->type(CouponType::Percentage)->amount(50)->set('products', ['456']))->save();

        $cart = Cart::make()->coupon($coupon->id())->lineItems([
            ['id' => 'abc', 'product' => '123', 'quantity' => 1, 'total' => 2500, 'discount_amount' => 1250], // This product is no longer eligible.
        ]);

        $cart = app(ApplyCouponDiscounts::class)->handle($cart, fn ($cart) => $cart);

        $this->assertNull($cart->coupon());
        $this->assertEquals($cart->couponTotal(), 0);
        $this->assertNull($cart->lineItems()->find('abc')->get('discount_amount'));
    }

    protected function makeProduct($id = null)
    {
        Collection::make('products')->save();

        return tap(Entry::make()->collection('products')->id($id))->save();
    }
}