<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tests\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Foundation\Http\FormRequest;
use Statamic\Facades\Stache;

class CartControllerTest extends TestCase
{
    use SetupCollections;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupCollections();
        $this->useBasicTaxEngine();
    }

    /** @test */
    public function can_get_cart_index()
    {
        $cart = Order::create()->save();

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
        $cart = Order::create()->save();

        $data = [
            'shipping_note' => 'Be careful pls.',
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart.update'), $data);

        $response->assertRedirect('/cart');

        $cart = $cart->fresh();

        $this->assertSame($cart->get('shipping_note'), 'Be careful pls.');
    }

    /** @test */
    public function can_update_cart_and_request_json_response()
    {
        $cart = Order::create()->save();

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

        $cart = $cart->fresh();

        $this->assertSame($cart->get('shipping_note'), 'Be careful pls.');
    }

    /** @test */
    public function can_update_cart_and_ensure_custom_form_request_is_used()
    {
        $cart = Order::create()->save();

        $data = [
            '_request' => CartUpdateFormRequest::class,
            'shipping_note' => 'Be careful pls.',
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart.update'), $data)
            ->assertSessionHasErrors('shipping_special');

        $this->assertEquals(session('errors')->default->first('shipping_special'), 'Coolzies. An error message.');

        $response->assertRedirect('/cart');

        $cart = $cart->fresh();

        $this->assertArrayNotHasKey('shipping_note', $cart->data());
    }

    /** @test */
    public function can_update_cart_and_ensure_custom_form_request_is_used_and_request_is_not_saved_to_order()
    {
        $cart = Order::create()->save();

        $data = [
            '_request' => CartUpdateWithNoRulesFormRequest::class,
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart.update'), $data)
            ->assertRedirect('/cart');

        $cart = $cart->fresh();

        $this->assertArrayNotHasKey('_request', $cart->data());
    }

    /** @test */
    public function can_update_cart_with_customer_already_in_cart()
    {
        $customer = Customer::create()->data([
            'name'  => 'Dan Smith',
            'email' => 'dan.smith@example.com',
        ])->save();

        $cart = Order::create(['customer' => $customer->id])->save();

        $data = [
            'shipping_note' => 'Be careful pls.',
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart.update'), $data);

        $response->assertRedirect('/cart');

        $cart = $cart->fresh();

        $this->assertSame($cart->get('shipping_note'), 'Be careful pls.');
        $this->assertSame($cart->get('customer'), $customer->id);
    }

    /** @test */
    public function can_update_cart_and_create_new_customer()
    {
        $this->markTestSkipped();

        $cart = Order::create()->save();

        $data = [
            'name'  => 'Joe Doe',
            'email' => 'joedoe@gmail.com',
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart.update'), $data);

        $response->assertRedirect('/cart');

        $cart = $cart->fresh();
        $customer = Customer::findByEmail($data['email']);

        $this->assertSame($cart->get('customer'), $customer->id);
        $this->assertSame($customer->name(), 'Joe Doe');
        $this->assertSame($customer->email(), 'joedoe@gmail.com');
    }

    /** @test */
    public function can_update_cart_and_existing_customer_by_id()
    {
        $customer = Customer::create()->data([
            'name'  => 'Jordan Smith',
            'email' => 'jordan.smith@example.com',
        ])->save();

        $cart = Order::create(['customer' => $customer->id])->save();

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

        $cart = $cart->fresh();

        $this->assertSame($cart->get('customer'), $customer->id);
        $this->assertSame($customer->get('name'), 'Jordan Smith');
    }

    /** @test */
    public function can_update_cart_and_existing_customer_by_email()
    {
        $this->markTestSkipped();

        $customer = Customer::create()->data([
            'name'  => 'Jak Simpson',
            'email' => 'jack.simpson@example.com',
        ])->save();

        $cart = Order::create()->save();

        $data = [
            'customer' => [
                'name'  => 'Jack Simpson',
                'email' => 'jack.simpson@example.com',
            ],
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart.update'), $data);

        $response->assertRedirect('/cart');

        $cart = $cart->fresh();
        $customer = Customer::findByEmail('jack.simpson@example.com');

        $this->assertSame($cart->get('customer'), $customer->id);
        $this->assertSame($customer->get('name'), 'Jack Simpson');
    }

    /** @test */
    public function can_update_cart_and_create_new_customer_via_customer_array()
    {
        $this->markTestSkipped();

        $cart = Order::create()->save();

        $data = [
            'customer' => [
                'name'  => 'Rebecca Logan',
                'email' => 'rebecca.logan@example.com',
            ],
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart.update'), $data);

        $response->assertRedirect('/cart');

        $cart = $cart->fresh();
        $customer = Customer::findByEmail('rebecca.logan@example.com');

        $this->assertTrue($cart->has('customer'));
        $this->assertIsString($cart->get('customer'));
        $this->assertSame($customer->name(), 'Rebecca Logan');
        $this->assertSame($customer->email(), 'rebecca.logan@example.com');
    }

    /**
     * @test
     * PR: https://github.com/doublethreedigital/simple-commerce/pull/337
     */
    public function can_update_cart_and_ensure_customer_is_not_overwritten()
    {
        $this->markTestSkipped();

        $customer = Customer::create([
            'name'  => 'Duncan',
            'email' => 'duncan@test.com',
        ])->save();

        $order = Order::create([
            'customer' => $customer->id,
        ])->save();

        $this->assertSame($customer->get('name'), 'Duncan');
        $this->assertSame($customer->id, $order->get('customer'));

        $cart = Order::create()->save();

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
        $cart = Order::create()->save();

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
        $product = Product::make()
            ->data(['price' => 1000]);

        $product->save();

        $cart = Order::create()
            ->save()
            ->set(
                'items',
                [
                    [
                        'id'       => Stache::generateId(),
                        'product'  => $product->id,
                        'quantity' => 1,
                        'total'    => 1000,
                    ],
                ],
            )
            ->save();

        $response = $this
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->delete(route('statamic.simple-commerce.cart.empty'));

        $response->assertRedirect();

        $cart = $cart->fresh();

        $this->assertSame($cart->get('items'), []);
    }

    /** @test */
    public function can_destroy_cart_and_request_json_response()
    {
        $product = Product::make()
            ->data(['price' => 1000]);

        $product->save();

        $cart = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 1000,
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

        $cart = $cart->fresh();

        $this->assertSame($cart->get('items'), []);
    }
}

class CartUpdateFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'shipping_special' => ['required', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            'shipping_special.required' => 'Coolzies. An error message.',
        ];
    }
}

class CartUpdateWithNoRulesFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }

    public function messages()
    {
        return [];
    }
}
