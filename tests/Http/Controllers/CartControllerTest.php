<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Tests\CollectionSetup;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class CartControllerTest extends TestCase
{
    use CollectionSetup;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupCollections();
    }

    /** @test */
    public function can_get_cart_index()
    {
        $cart = Cart::make()->save();

        $response = $this
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->get(route('statamic.simple-commerce.cart.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'title',
                'items',
                'is_paid',
                'grand_total',
                'items_total',
                'tax_total',
                'shipping_total',
                'coupon_total',
            ]);
    }

    /** @test */
    public function can_update_cart()
    {
        $cart = Cart::make()->save();

        $data = [
            'shipping_note' => 'Be careful pls.',
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart.update'), $data);

        $response->assertRedirect('/cart');

        $cart->find($cart->id);

        $this->assertSame($cart->data['shipping_note'], 'Be careful pls.');
    }

    /** @test */
    public function can_update_cart_with_customer_details()
    {
        $cart = Cart::make()->save();

        $data = [
            'customer' => [
                'name' => 'John Doe',
                'email' => 'johndoe@gmail.com',
            ],
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart.update'), $data);

            $this->withoutExceptionHandling();
            dd($response);

        $response->assertRedirect('/cart');

        $cart->find($cart->id);
        $customer = Customer::findByEmail($data['customer']['email']);

        $this->assertSame($cart->data['customer'], $customer->id);
    }

    /** @test */
    public function can_update_cart_with_custom_redirect_page()
    {
        $cart = Cart::make()->save();

        $data = [
            '_redirect' => '/checkout',
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart.update'), $data);

        $response->assertRedirect('/checkout');
    }

    /** @test */
    public function can_destroy_cart()
    {
        //
    }
}
