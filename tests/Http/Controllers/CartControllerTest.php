<?php

use DuncanMcClean\SimpleCommerce\Exceptions\CustomerNotFound;
use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Http\Requests\CartUpdateFormRequest;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Http\Requests\CartUpdateWithNoRulesFormRequest;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\RefreshContent;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Stache;
use Statamic\Facades\User;

use function PHPUnit\Framework\assertSame;

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

    expect('Be careful pls.')->toBe($cart->get('shipping_note'));
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

    expect('Be careful pls.')->toBe($cart->get('shipping_note'));
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

    expect($cart->get('shipping_note'))->toBeNull();
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

    expect('Coolzies. An error message.')->toEqual(session('errors')->default->first('shipping_special'));

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

    expect('Be careful pls.')->toBe($cart->get('shipping_note'));
    expect($customer->id)->toBe($cart->customer()->id());
});

test('can update cart with customer already in cart and ensure the customer email is updated', function () {
    $customer = Customer::make()
        ->email('dan.smith@example.com')
        ->data([
            'name' => 'Dan Smith',
        ]);

    $customer->save();

    $cart = Order::make()->customer($customer->id);
    $cart->save();

    $data = [
        'customer' => ['email' => 'dan@smith.test'],
    ];

    $response = $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart.update'), $data);

    $response->assertRedirect('/cart');

    $cart = $cart->fresh();

    expect($customer->id)->toBe($cart->customer()->id());
    expect('dan@smith.test')->toBe($cart->customer()->email());
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

    expect($customer->id)->toBe($cart->customer()->id());
    expect('1st January 1980')->toBe($cart->customer()->get('dob'));
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

    expect($customer->id)->toBe($cart->customer()->id);
    expect('Joe Doe')->toBe($customer->name());
    expect('joedoe@gmail.com')->toBe($customer->email());
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

    expect($customer->id)->toBe($cart->customer()->id);
    expect('Joe Doe')->toBe($customer->name());
    expect('joedoe@gmail.com')->toBe($customer->email());
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

        expect(false)->toBeTrue();
    } catch (CustomerNotFound $e) {
        expect(true)->toBeTrue();
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

    expect($customer->id)->toBe($cart->customer()->id());
    expect('Jordan Smith')->toBe($customer->get('name'));
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

    expect($customer->id)->toBe($cart->customer()->id());
    expect('Jack Simpson')->toBe($customer->get('name'));
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

    expect($customer->id)->toBe($cart->customer()->id());
    expect('Jack Simpson')->toBe($customer->get('name'));
    expect('1st January 1980')->toBe($cart->customer()->get('dob'));
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

    expect($customer->id)->toBe($cart->customer()->id);
    expect('Rebecca Logan')->toBe($customer->name());
    expect('rebecca.logan@example.com')->toBe($customer->email());
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

    expect($customer->id)->toBe($cart->customer()->id);
    expect('Rebecca Logan')->toBe($customer->name());
    expect('rebecca.logan@example.com')->toBe($customer->email());
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

    expect($customer->id)->toBe($cart->customer()->id);
    expect('Rebecca Logan')->toBe($customer->name());
    expect('rebecca.logan@example.com')->toBe($customer->email());
    expect('1st January 1980')->toBe($cart->customer()->get('dob'));
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

    expect($cart->customer())->toBeNull();

    try {
        Customer::findByEmail('cj cregg@example.com');

        expect(false)->toBeTrue();
    } catch (CustomerNotFound $e) {
        expect(true)->toBeTrue();
    }
});

/**
 * PR: https://github.com/duncanmcclean/simple-commerce/pull/337
 */
test('can update cart and ensure customer is not overwritten', function () {
    $customer = Customer::make()->email('duncan@test.com')->data([
        'name' => 'Duncan',
    ]);

    $customer->save();

    $order = Order::make()->customer($customer->id);
    $order->save();

    expect('Duncan')->toBe($customer->get('name'));
    expect($order->customer())->toBe($customer->id);

    $cart = Order::make();
    $cart->save();

    $data = [
        'email' => 'duncan@test.com',
    ];

    $response = $this
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart.update'), $data);

    $cartCustomer = Customer::find($cart->resource()->customer());

    expect($cartCustomer->id)->toBe($customer->id);
    expect($cartCustomer->get('name'))->toBe($customer->get('name'));
})->skip();

test('can update cart and use logged in user as customer', function () {
    setupUserCustomerRepository();

    $cart = Order::make();
    $cart->save();

    $user = User::make()->email('james@example.com')->set('name', 'James Test');
    $user->save();

    $response = $this
        ->actingAs($user)
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart.update'));

    $response->assertRedirect('/cart');

    $cart = $cart->fresh();

    assertSame($cart->customer()->id(), $user->id());
    assertSame($cart->customer()->name(), $user->name);
    assertSame($cart->customer()->email(), $user->email);

    tearDownUserCustomerRepository();
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

    expect([])->toBe($cart->lineItems()->toArray());
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
    ]);

    $cart = $cart->fresh();

    expect([])->toBe($cart->lineItems()->toArray());
});
