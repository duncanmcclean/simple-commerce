<?php

namespace Tests\Feature\Coupons;

use DuncanMcClean\SimpleCommerce\Coupons\CouponType;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function is_valid_when_line_item_product_is_in_allowlist()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $cart = Cart::make()->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $coupon = Coupon::make()
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10)
            ->set('products', ['product-1']);

        $this->assertTrue($coupon->isValid($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_not_valid_when_line_item_product_is_not_in_allowlist()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $cart = Cart::make()->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $coupon = Coupon::make()
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10)
            ->set('products', ['product-2']);

        $this->assertFalse($coupon->isValid($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_valid_when_customer_is_in_allowlist()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $user = tap(User::make()->email('cj.cregg@example.com'))->save();

        $cart = Cart::make()->customer($user)->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $coupon = Coupon::make()
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10)
            ->set('customer_eligibility', 'specific_customers')
            ->set('customers', [$user->id()]);

        $this->assertTrue($coupon->isValid($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_not_valid_when_customer_is_not_in_allowlist()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $user = tap(User::make()->email('cj.cregg@example.com'))->save();

        $cart = Cart::make()->customer($user)->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $coupon = Coupon::make()
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10)
            ->set('customer_eligibility', 'specific_customers')
            ->set('customers', ['abc']);

        $this->assertFalse($coupon->isValid($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_not_valid_when_customer_is_a_guest()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $cart = Cart::make()
            ->customer([
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
            ])
            ->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $coupon = Coupon::make()
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10)
            ->set('customer_eligibility', 'specific_customers')
            ->set('customers', ['abc']);

        $this->assertFalse($coupon->isValid($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_valid_when_customer_email_matches_domain()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $user = tap(User::make()->email('cj.cregg@example.com'))->save();

        $cart = Cart::make()->customer($user)->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $coupon = Coupon::make()
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10)
            ->set('customer_eligibility', 'customers_by_domain')
            ->set('customers_by_domain', ['example.com']);

        $this->assertTrue($coupon->isValid($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_not_valid_when_customer_email_does_not_match_domain()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $user = tap(User::make()->email('cj.cregg@example.com'))->save();

        $cart = Cart::make()->customer($user)->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $coupon = Coupon::make()
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10)
            ->set('customer_eligibility', 'customers_by_domain')
            ->set('customers_by_domain', ['statamic.com']);

        $this->assertFalse($coupon->isValid($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_not_valid_before_valid_from_timestamp()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $cart = Cart::make()->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $coupon = Coupon::make()
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10)
            ->set('valid_from', '2030-01-01');

        $this->assertFalse($coupon->isValid($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_valid_between_timestamps()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $cart = Cart::make()->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $coupon = Coupon::make()
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10)
            ->set('valid_from', '2024-01-01')
            ->set('expires_at', '2030-01-01');

        $this->assertTrue($coupon->isValid($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_not_valid_after_coupon_has_expired()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $cart = Cart::make()->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $coupon = Coupon::make()
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10)
            ->set('expires_at', '2024-01-01');

        $this->assertFalse($coupon->isValid($cart, $cart->lineItems()->first()));
    }
}
