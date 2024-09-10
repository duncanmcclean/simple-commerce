<?php

namespace Tests\Feature\Customers;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Statamic\Facades\Blink;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class AssignUserToCartTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Cart::forgetCurrentCart();
    }

    #[Test]
    public function user_is_assigned_to_cart_when_they_have_no_recent_carts()
    {
        $cart = $this->makeCart();
        $user = User::make()->save();

        $this->assertNull($cart->customer());

        Auth::login($user);

        $this->assertEquals($user->id(), $cart->fresh()->customer()->id());
    }

    #[Test]
    public function the_recent_cart_is_made_the_current_cart_when_no_current_cart_is_set()
    {
        $user = User::make()->save();
        $recentCart = tap(Cart::make()->customer($user))->save();

        // When the Cart is saved above, it'll set the current cart, which we don't want for this test.
        Blink::forget(config('simple-commerce.carts.cookie_name'));

        $this->assertFalse(Cart::hasCurrentCart());

        Auth::login($user);

        $this->assertEquals($recentCart->id(), Cart::current()->id());
    }

    #[Test]
    public function the_current_cart_is_merged_into_the_recent_cart()
    {
        Config::set('simple-commerce.carts.merge_on_login', true);

        $user = User::make()->save();

        $productOne = $this->makeProduct();
        $productTwo = $this->makeProduct();

        $recentCart = tap(Cart::make()->customer($user))->save();
        $recentCart->lineItems()->create(['product' => $productOne->id(), 'quantity' => 1]);

        $currentCart = $this->makeCart();
        $currentCart->lineItems()->create(['product' => $productTwo->id(), 'quantity' => 2]);

        Auth::login($user);

        $this->assertEquals($recentCart->id(), Cart::current()->id());

        $this->assertCount(2, $recentCart->lineItems());

        $this->assertEquals(1, $recentCart->lineItems()->first()->quantity());
        $this->assertEquals($productOne->id(), $recentCart->lineItems()->first()->product()->id());

        $this->assertEquals(2, $recentCart->lineItems()->last()->quantity());
        $this->assertEquals($productTwo->id(), $recentCart->lineItems()->last()->product()->id());
    }

    #[Test]
    public function the_recent_cart_is_deleted()
    {
        Config::set('simple-commerce.carts.merge_on_login', false);

        $user = User::make()->save();

        $recentCart = tap(Cart::make()->customer($user))->save();

        $currentCart = $this->makeCart();
        $currentCart->lineItems()->create(['product' => $this->makeProduct()->id(), 'quantity' => 1]);

        Auth::login($user);

        $this->assertNull(Cart::find($recentCart->id()));
        $this->assertNotNull(Cart::find($currentCart->id()));
    }

    protected function makeCart()
    {
        $cart = tap(Cart::make())->save();

        Cart::setCurrent($cart);

        return $cart;
    }

    protected function makeProduct($id = null)
    {
        Collection::make('products')->save();

        return tap(Entry::make()->collection('products')->id($id))->save();
    }
}