<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
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
        $cart = Cart::create()->save();

        $response = $this
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->getJson(route('statamic.simple-commerce.cart.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data',
            ]);
    }

    /** @test */
    public function can_update_cart()
    {
        $cart = Cart::create()->save();

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
    public function can_update_cart_and_request_json_response()
    {
        $cart = Cart::create()->save();

        $data = [
            'shipping_note' => 'Be careful pls.',
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->postJson(route('statamic.simple-commerce.cart.update'), $data);

        $response->assertJsonStructure([
            'status',
            'message',
            'cart',
        ]);

        $cart->find($cart->id);

        $this->assertSame($cart->data['shipping_note'], 'Be careful pls.');
    }

    /** @test */
    public function can_update_cart_with_customer_already_in_cart()
    {
        $customer = Customer::create()->data([
            'name' => 'Dan Smith',
            'email' => 'dan.smith@example.com',
        ])->save();

        $cart = Cart::create()->save()->data(['customer' => $customer->id])->save();

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
        $cart = Cart::create()->save();

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
        $customer = Customer::create()->data([
            'name' => 'Jordan Smith',
            'email' => 'jordan.smith@example.com',
        ])->save();

        $cart = Cart::create()->save()->data(['customer' => $customer->id])->save();

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

        $this->assertSame($cart->data['customer'], $customer->id);
        $this->assertSame($customer->data['name'], 'Jordan Smith');
    }

    /** @test */
    public function can_update_cart_and_existing_customer_by_email()
    {
        $customer = Customer::create()->data([
            'name' => 'Jak Simpson',
            'email' => 'jack.simpson@example.com',
        ])->save();

        $cart = Cart::create()->save();

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
        $cart = Cart::create()->save();

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

    /**
     * @test
     * PR: https://github.com/doublethreedigital/simple-commerce/pull/337
     */
    public function can_update_cart_and_ensure_customer_is_not_overwritten()
    {
        $customer = Customer::create([
            'name' => 'Duncan',
            'email' => 'duncan@test.com',
        ])->save();

        $order = Order::create([
            'customer' => $customer->id,
        ])->save();

        $this->assertSame($customer->get('name'), 'Duncan');
        $this->assertSame($customer->id, $order->get('customer'));

        $cart = Cart::create()->save();

        $data = [
            'email' => 'duncan@test.com',
        ];

        $response = $this
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart.update'), $data);

        $cartCustomer = Customer::find($cart->entry()->get('customer'));

        $this->assertSame($customer->id, $cartCustomer->id);
        $this->assertSame($customer->get('name'), $cartCustomer->get('name'));
    }

    /** @test */
    public function can_update_cart_with_custom_redirect_page()
    {
        $cart = Cart::create()->save();

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
        $product = Product::create()->save()->data(['price' => 1000])->save();

        $cart = Cart::create()
            ->save()
            ->data([
                'items' => [
                    [
                        'id' => Stache::generateId(),
                        'product' => $product->id,
                        'quantity' => 1,
                        'total' => 1000,
                    ],
                ],
            ])
            ->save();

        $response = $this
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->delete(route('statamic.simple-commerce.cart.empty'));

        $response->assertRedirect();

        $cart->find($cart->id);

        $this->assertSame($cart->data['items'], []);
    }

    /** @test */
    public function can_destroy_cart_and_request_json_response()
    {
        $product = Product::create(['price' => 1000])->save();

        $cart = Cart::create([
                'items' => [
                    [
                        'id' => Stache::generateId(),
                        'product' => $product->id,
                        'quantity' => 1,
                        'total' => 1000,
                    ],
                ],
            ])->save();

        $response = $this
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->deleteJson(route('statamic.simple-commerce.cart.empty'));

        $response->assertJsonStructure([
            'status',
            'message',
            'cart',
        ]);

        $cart->find($cart->id);

        $this->assertSame($cart->data['items'], []);
    }
}
