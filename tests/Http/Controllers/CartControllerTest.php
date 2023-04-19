<?php

use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\RefreshContent;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Stache;

uses(TestCase::class);
uses(SetupCollections::class);
uses(RefreshContent::class);
beforeEach(function () {
    $this->useBasicTaxEngine();
});


test('can get cart index', function () {
    $cart = Order::make();
    $cart->save();

    $response = $this
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->getJson(route('statamic.simple-commerce.cart.index'));

    $response->assertOk()
        ->assertJsonStructure([
            'data',
        ]);
});

test('can update cart', function () {
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
});

test('can update cart and request json response', function () {
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
});

test('cant update cart if fields not whitelisted in config', function () {
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
});

test('can update cart and ensure custom form request is used', function () {
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
});

test('can update cart and ensure custom form request is used and request is not saved to order', function () {
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
});

test('can update cart with customer already in cart', function () {
    Config::set('simple-commerce.field_whitelist.orders', ['shipping_note']);

    $customer = Customer::make()
        ->email('dan.smith@example.com')
        ->data([
            'name' => 'Dan Smith',
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
});

/**
 * https://github.com/duncanmcclean/simple-commerce/issues/658
 */
test('can update cart with customer already in cart with additional data', function () {
    Config::set('simple-commerce.field_whitelist.orders', ['shipping_note']);

    Config::set('simple-commerce.field_whitelist.customers', [
        'name', 'email', 'dob',
    ]);

    $customer = Customer::make()
        ->email('dan.smith@example.com')
        ->data([
            'name' => 'Dan Smith',
        ]);

    $customer->save();

    $cart = Order::make()->customer($customer->id);
    $cart->save();

    $data = [
        'customer' => [
            'dob' => '1st January 1980',
        ],
    ];

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart.update'), $data);

    $response->assertRedirect('/cart');

    $cart = $cart->fresh();

    $this->assertSame($cart->customer()->id(), $customer->id);
    $this->assertSame($cart->customer()->get('dob'), '1st January 1980');
});

test('can update cart and create new customer', function () {
    Config::set('simple-commerce.field_whitelist.orders', ['shipping_note']);

    $cart = Order::make();
    $cart->save();

    $data = [
        'name' => 'Joe Doe',
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
});

test('can update cart and create new customer with first name and last name', function () {
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
});

test('cant update cart and create new customer if email contains spaces', function () {
    $cart = Order::make();
    $cart->save();

    $data = [
        'name' => 'Joe Mo',
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
});

test('can update cart and existing customer by id', function () {
    $customer = Customer::make()->email('jordan.smith@example.com')->data([
        'name' => 'Jordan Smith',
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
});

test('can update cart and existing customer by email', function () {
    $customer = Customer::make()->email('jack.simpson@example.com')->data([
        'name' => 'Jak Simpson',
    ]);

    $customer->save();

    $cart = Order::make();
    $cart->save();

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

    $cart = $cart->fresh();

    $customer = Customer::findByEmail('jack.simpson@example.com');

    $this->assertSame($cart->customer()->id(), $customer->id);
    $this->assertSame($customer->get('name'), 'Jack Simpson');
});

test('can update cart and existing customer by email with additional data', function () {
    Config::set('simple-commerce.field_whitelist.customers', [
        'name', 'email', 'dob',
    ]);

    $customer = Customer::make()->email('jack.simpson@example.com')->data([
        'name' => 'Jak Simpson',
    ]);

    $customer->save();

    $cart = Order::make();
    $cart->save();

    $data = [
        'customer' => [
            'name' => 'Jack Simpson',
            'email' => 'jack.simpson@example.com',
            'dob' => '1st January 1980',
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
    $this->assertSame($cart->customer()->get('dob'), '1st January 1980');
});

test('can update cart and create new customer via customer array', function () {
    $cart = Order::make();
    $cart->save();

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

    $cart = $cart->fresh();
    $customer = Customer::findByEmail('rebecca.logan@example.com');

    $this->assertSame($cart->customer()->id, $customer->id);
    $this->assertSame($customer->name(), 'Rebecca Logan');
    $this->assertSame($customer->email(), 'rebecca.logan@example.com');
});

test('can update cart and create new customer via customer array with first name and last name', function () {
    Config::set('simple-commerce.field_whitelist.customers', [
        'first_name', 'last_name',
    ]);

    $cart = Order::make();
    $cart->save();

    $data = [
        'customer' => [
            'first_name' => 'Rebecca',
            'last_name' => 'Logan',
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
});

test('can update cart and create new customer via customer array with additional data', function () {
    Config::set('simple-commerce.field_whitelist.customers', [
        'name', 'email', 'dob',
    ]);

    $cart = Order::make();
    $cart->save();

    $data = [
        'customer' => [
            'name' => 'Rebecca Logan',
            'email' => 'rebecca.logan@example.com',
            'dob' => '1st January 1980',
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
    $this->assertSame($cart->customer()->get('dob'), '1st January 1980');
});

test('cant update cart and create new customer via customer array if email contains spaces', function () {
    $cart = Order::make();
    $cart->save();

    $data = [
        'customer' => [
            'name' => 'CJ Cregg',
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
});

/**
 * PR: https://github.com/duncanmcclean/simple-commerce/pull/337
 */
test('can update cart and ensure customer is not overwritten', function () {
    $this->markTestSkipped();

    $customer = Customer::make()->email('duncan@test.com')->data([
        'name' => 'Duncan',
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
});

test('can update cart with custom redirect page', function () {
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
});

test('can destroy cart', function () {
    $product = Product::make()->price(1000);
    $product->save();

    $cart = Order::make()
        ->set(
            'items',
            [
                [
                    'id' => Stache::generateId(),
                    'product' => $product->id,
                    'quantity' => 1,
                    'total' => 1000,
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
});

test('can destroy cart and request json response', function () {
    $product = Product::make()->price(1000);
    $product->save();

    $cart = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 1000,
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
});

// Helpers
function authorize()
{
    return true;
}

function rules()
{
    return [];
}

function messages()
{
    return [];
}
