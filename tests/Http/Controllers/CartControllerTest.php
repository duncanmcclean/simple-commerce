<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tests\CollectionSetup;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Statamic\Facades\Stache;

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
    public function can_update_cart_with_customer_already_in_cart()
    {
        $customer = Customer::make()->data([
            'name' => 'Dan Smith',
            'email' => 'dan.smith@example.com',
        ])->save();

        $cart = Cart::make()->save()->update(['customer' => $customer->id]);

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
        $this->assertSame($cart->data['customer'], $customer->id);
    }

    /** @test */
    public function can_update_cart_and_create_new_customer()
    {
        $cart = Cart::make()->save();

        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@gmail.com',
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart.update'), $data);

        $response->assertRedirect('/cart');

        $cart->find($cart->id);
        $customer = Customer::findByEmail($data['email']);

        $this->assertSame($cart->data['customer'], $customer->id);
        $this->assertSame($customer->title, 'John Doe <johndoe@gmail.com>');
        $this->assertSame($customer->slug, 'johndoe-at-gmailcom');
    }

    /** @test */
    public function can_update_cart_and_existing_customer_by_id()
    {
        $customer = Customer::make()->data([
            'name' => 'Jor Smith',
            'email' => 'jordan.smith@example.com',
        ])->save();

        $cart = Cart::make()->save()->update(['customer' => $customer->id]);

        $data = [
            'customer' => [
                'name' => 'Jordan Smith',
            ],
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart.update'), $data);

        $response->assertRedirect('/cart');

        $cart->find($cart->id);
        $customer = Customer::findByEmail('jordan.smith@example.com');

        $this->assertSame($cart->data['customer'], $customer->id);
        $this->assertSame($customer->data['name'], 'Jordan Smith');
    }

    /** @test */
    public function can_update_cart_and_existing_customer_by_email()
    {
        $customer = Customer::make()->data([
            'name' => 'Jak Simpson',
            'email' => 'jack.simpson@example.com',
        ])->save();

        $cart = Cart::make()->save();

        $data = [
            'customer' => [
                'name' => 'Jack Simpson',
                'email' => 'jack.simpson@example.com',
            ],
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart.update'), $data);

        $response->assertRedirect('/cart');

        $cart->find($cart->id);
        $customer = Customer::findByEmail('jack.simpson@example.com');

        $this->assertSame($cart->data['customer'], $customer->id);
        $this->assertSame($customer->data['name'], 'Jack Simpson');
    }

    /** @test */
    public function can_update_cart_and_create_new_customer_via_customer_array()
    {
        $cart = Cart::make()->save();

        $data = [
            'customer' => [
                'name' => 'Rebecca Logan',
                'email' => 'rebecca.logan@example.com',
            ],
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart.update'), $data);

        $response->assertRedirect('/cart');

        $cart->find($cart->id);
        $customer = Customer::findByEmail('rebecca.logan@example.com');

        $this->assertTrue(isset($cart->data['customer']));
        $this->assertIsString($cart->data['customer']);
        $this->assertSame($customer->data['name'], 'Rebecca Logan');
        $this->assertSame($customer->title, 'Rebecca Logan <rebecca.logan@example.com>');
        $this->assertSame($customer->slug, 'rebeccalogan-at-examplecom');
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
        $product = Product::make()->save()->update(['price' => 1000]);

        $cart = Cart::make()
            ->save()
            ->update([
                'items' => [
                    [
                        'id' => Stache::generateId(),
                        'product' => $product->id,
                        'quantity' => 1,
                        'total' => 1000,
                    ],
                ],
            ]);

        $response = $this
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->delete(route('statamic.simple-commerce.cart.empty'));

        $response->assertRedirect();

        $cart->find($cart->id);

        $this->assertSame($cart->data['items'], []);
    }
}
