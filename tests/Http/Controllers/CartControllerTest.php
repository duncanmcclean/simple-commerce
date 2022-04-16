<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tests\RefreshContent;
use DoubleThreeDigital\SimpleCommerce\Tests\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Stache;

class CartControllerTest extends TestCase
{
    use SetupCollections, RefreshContent;

    public function setUp(): void
    {
        parent::setUp();

        $this->useBasicTaxEngine();
    }

    /** @test */
    public function can_get_cart_index()
    {
        $cart = Order::make();
        $cart->save();

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
        Config::set('simple-commerce.field_whitelist.orders', ['shipping_note']);

        $cart = Order::make();
        $cart->save();

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
        Config::set('simple-commerce.field_whitelist.orders', ['shipping_note']);

        $cart = Order::make();
        $cart->save();

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
    public function cant_update_cart_if_fields_not_whitelisted_in_config()
    {
        Config::set('simple-commerce.field_whitelist.orders', []);

        $cart = Order::make();
        $cart->save();

        $data = [
            'shipping_note' => 'Be careful pls.',
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart.update'), $data);

        $response->assertRedirect('/cart');

        $cart = $cart->fresh();

        $this->assertNull($cart->get('shipping_note'));
    }

    /** @test */
    public function can_update_cart_and_ensure_custom_form_request_is_used()
    {
        Config::set('simple-commerce.field_whitelist.orders', ['shipping_note']);

        $cart = Order::make();
        $cart->save();

        $data = [
            '_request' => encrypt(CartUpdateFormRequest::class),
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
        $cart = Order::make();
        $cart->save();

        $data = [
            '_request' => encrypt(CartUpdateWithNoRulesFormRequest::class),
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
        Config::set('simple-commerce.field_whitelist.orders', ['shipping_note']);

        $customer = Customer::make()
            ->email('dan.smith@example.com')
            ->data([
                'name'  => 'Dan Smith',
            ]);

        $customer->save();

        $cart = Order::make()->customer($customer->id);
        $cart->save();

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
        $this->assertSame($cart->customer()->id(), $customer->id);
    }

    /** @test */
    public function can_update_cart_and_create_new_customer()
    {
        Config::set('simple-commerce.field_whitelist.orders', ['shipping_note']);

        $cart = Order::make();
        $cart->save();

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

        $this->assertSame($cart->customer()->id, $customer->id);
        $this->assertSame($customer->name(), 'Joe Doe');
        $this->assertSame($customer->email(), 'joedoe@gmail.com');
    }

    /** @test */
    public function can_update_cart_and_create_new_customer_with_first_name_and_last_name()
    {
        Config::set('simple-commerce.field_whitelist.orders', ['shipping_note']);

        $cart = Order::make();
        $cart->save();

        $data = [
            'first_name' => 'Joe',
            'last_name' => 'Doe',
            'email' => 'joedoe@gmail.com',
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart.update'), $data);

        $response->assertRedirect('/cart');

        $cart = $cart->fresh();
        $customer = Customer::findByEmail($data['email']);

        $this->assertSame($cart->customer()->id, $customer->id);
        $this->assertSame($customer->name(), 'Joe Doe');
        $this->assertSame($customer->email(), 'joedoe@gmail.com');
    }

    /** @test */
    public function cant_update_cart_and_create_new_customer_if_email_contains_spaces()
    {
        $cart = Order::make();
        $cart->save();

        $data = [
            'name'  => 'Joe Mo',
            'email' => 'joe mo@gmail.com',
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart.update'), $data)
            ->assertSessionHasErrors('email');

        $this->assertArrayNotHasKey('customer', $cart->data);

        try {
            Customer::findByEmail($data['email']);

            $this->assertTrue(false);
        } catch (CustomerNotFound $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function can_update_cart_and_existing_customer_by_id()
    {
        $customer = Customer::make()->email('jordan.smith@example.com')->data([
            'name'  => 'Jordan Smith',
        ]);

        $customer->save();

        $cart = Order::make()->customer($customer->id);
        $cart->save();

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

        $this->assertSame($cart->customer()->id(), $customer->id);
        $this->assertSame($customer->get('name'), 'Jordan Smith');
    }

    /** @test */
    public function can_update_cart_and_existing_customer_by_email()
    {
        $customer = Customer::make()->email('jack.simpson@example.com')->data([
            'name'  => 'Jak Simpson',
        ]);

        $customer->save();

        $cart = Order::make();
        $cart->save();

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

        $this->assertSame($cart->customer()->id(), $customer->id);
        $this->assertSame($customer->get('name'), 'Jack Simpson');
    }

    /** @test */
    public function can_update_cart_and_create_new_customer_via_customer_array()
    {
        $cart = Order::make();
        $cart->save();

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

        $this->assertSame($cart->customer()->id, $customer->id);
        $this->assertSame($customer->name(), 'Rebecca Logan');
        $this->assertSame($customer->email(), 'rebecca.logan@example.com');
    }

    /** @test */
    public function can_update_cart_and_create_new_customer_via_customer_array_with_first_name_and_last_name()
    {
        $cart = Order::make();
        $cart->save();

        $data = [
            'customer' => [
                'first_name'  => 'Rebecca',
                'last_name'   => 'Logan',
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

        $this->assertSame($cart->customer()->id, $customer->id);
        $this->assertSame($customer->name(), 'Rebecca Logan');
        $this->assertSame($customer->email(), 'rebecca.logan@example.com');
    }

    /** @test */
    public function cant_update_cart_and_create_new_customer_via_customer_array_if_email_contains_spaces()
    {
        $cart = Order::make();
        $cart->save();

        $data = [
            'customer' => [
                'name'  => 'CJ Cregg',
                'email' => 'cj cregg@example.com',
            ],
        ];

        $response = $this
            ->from('/cart')
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart.update'), $data)
            ->assertSessionHasErrors();

        $cart->fresh();

        $this->assertNull($cart->customer());

        try {
            Customer::findByEmail('cj cregg@example.com');

            $this->assertTrue(false);
        } catch (CustomerNotFound $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @test
     * PR: https://github.com/doublethreedigital/simple-commerce/pull/337
     */
    public function can_update_cart_and_ensure_customer_is_not_overwritten()
    {
        $this->markTestSkipped();

        $customer = Customer::make()->email('duncan@test.com')->data([
            'name'  => 'Duncan',
        ]);

        $customer->save();

        $order = Order::make()->customer($customer->id);
        $order->save();

        $this->assertSame($customer->get('name'), 'Duncan');
        $this->assertSame($customer->id, $order->customer());

        $cart = Order::make();
        $cart->save();

        $data = [
            'email' => 'duncan@test.com',
        ];

        $response = $this
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->post(route('statamic.simple-commerce.cart.update'), $data);

        $cartCustomer = Customer::find($cart->resource()->customer());

        $this->assertSame($customer->id, $cartCustomer->id);
        $this->assertSame($customer->get('name'), $cartCustomer->get('name'));
    }

    /** @test */
    public function can_update_cart_with_custom_redirect_page()
    {
        $cart = Order::make();
        $cart->save();

        $data = [
            '_redirect' => encrypt('/checkout'),
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
        $product = Product::make()->price(1000);
        $product->save();

        $cart = Order::make()
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
            );

        $cart->save();

        $response = $this
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->delete(route('statamic.simple-commerce.cart.empty'));

        $response->assertRedirect();

        $cart = $cart->fresh();

        $this->assertSame($cart->lineItems()->toArray(), []);
    }

    /** @test */
    public function can_destroy_cart_and_request_json_response()
    {
        $product = Product::make()->price(1000);
        $product->save();

        $cart = Order::make()->lineItems([
            [
                'id'       => Stache::generateId(),
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 1000,
            ],
        ]);

        $cart->save();

        $response = $this
            ->withSession(['simple-commerce-cart' => $cart->id])
            ->deleteJson(route('statamic.simple-commerce.cart.empty'));

        $response->assertJsonStructure([
            'status',
            'message',
            'cart',
        ]);

        $cart = $cart->fresh();

        $this->assertSame($cart->lineItems()->toArray(), []);
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
