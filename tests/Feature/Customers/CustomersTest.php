<?php

namespace Tests\Feature\Customers;

use DuncanMcClean\SimpleCommerce\Customers\GuestCustomer;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Facades\UserGroup;
use Tests\TestCase;

class CustomersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cart::forgetCurrentCart();
    }

    #[Test]
    public function logged_in_user_is_assigned_to_cart()
    {
        $cart = $this->makeCart();
        $user = User::make()->save();

        $this->assertNull($cart->customer());

        $this
            ->actingAs($user)
            ->patch('/!/simple-commerce/cart')
            ->assertRedirect();

        $this->assertEquals($user->id(), $cart->fresh()->customer()->id());
    }

    #[Test]
    public function logged_in_user_can_be_updated()
    {
        $this->makeCart();
        $user = User::make()->set('name', 'John Doe')->email('john.doe@example.com')->save();

        $this
            ->actingAs($user)
            ->patch('/!/simple-commerce/cart', [
                'customer' => [
                    'name' => 'Jane Doe',
                    'email' => 'jane.doe@example.com',
                ],
            ])
            ->assertRedirect();

        $this->assertEquals('Jane Doe', $user->fresh()->name());
        $this->assertEquals('jane.doe@example.com', $user->fresh()->email());
    }

    #[Test]
    public function logged_in_user_cant_change_their_permissions()
    {
        $this->makeCart();
        $user = User::make()->save();

        Role::make('manager')->save();
        UserGroup::make('staff')->save();

        $this
            ->actingAs($user)
            ->patch('/!/simple-commerce/cart', [
                'customer' => [
                    'super' => true,
                    'roles' => ['manager'],
                    'groups' => ['staff'],
                ],
            ])
            ->assertRedirect();

        $this->assertFalse($user->fresh()->isSuper());
        $this->assertEmpty($user->fresh()->roles()->all());
        $this->assertEmpty($user->fresh()->groups()->all());
    }

    #[Test]
    public function guest_customer_is_assigned_to_cart()
    {
        $cart = $this->makeCart();

        $this->assertNull($cart->customer());

        $this
            ->patch('/!/simple-commerce/cart', [
                'customer' => [
                    'name' => 'John Doe',
                    'email' => 'john.doe@example.com',
                ],
            ])
            ->assertRedirect();

        $this->assertInstanceOf(GuestCustomer::class, $cart->fresh()->customer());
        $this->assertEquals('John Doe', $cart->fresh()->customer()->name());
        $this->assertEquals('john.doe@example.com', $cart->fresh()->customer()->email());
    }

    #[Test]
    public function guest_customer_can_be_updated()
    {
        $cart = $this->makeCart();
        $cart->customer(['name' => 'John Doe', 'email' => 'john.doe@example.com', 'foo' => 'bar'])->save();

        $this
            ->patch('/!/simple-commerce/cart', [
                'customer' => [
                    'name' => 'Jane Doe',
                    'email' => 'jane.doe@example.com',
                ],
            ])
            ->assertRedirect();

        $this->assertEquals('Jane Doe', $cart->fresh()->customer()->name());
        $this->assertEquals('jane.doe@example.com', $cart->fresh()->customer()->email());
    }

    protected function makeCart()
    {
        $cart = tap(Cart::make())->save();

        Cart::setCurrent($cart);

        return $cart;
    }
}
