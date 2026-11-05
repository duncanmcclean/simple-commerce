<?php

use DuncanMcClean\SimpleCommerce\Exceptions\CustomerNotFound;
use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Http\Requests\CartUpdateFormRequest;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Http\Requests\CartUpdateWithNoRulesFormRequest;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\RefreshContent;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Collection;
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

test('can update cart with form parameter validation disabled', function (?string $redirect, string $expectedRedirect) {
    app()->detectEnvironment(fn () => 'production');

    Config::set('simple-commerce.disable_form_parameter_validation', true);

    $cart = Order::make();
    $cart->save();

    $this
        ->withoutMiddleware(ValidateCsrfToken::class)
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart.update'), array_filter(['_redirect' => $redirect]))
        ->assertRedirect($expectedRedirect);
})->with([
    'without redirect' => [null, '/cart'],
    'with relative redirect' => ['/checkout', '/checkout'],
    'with redirect to another host' => ['https://evil.example', '/cart'],
]);

test('cant update cart with a custom form request that is not a form request', function () {
    app()->detectEnvironment(fn () => 'production');

    Config::set('simple-commerce.disable_form_parameter_validation', true);

    $cart = Order::make();
    $cart->save();

    $this
        ->withoutMiddleware(ValidateCsrfToken::class)
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart.update'), ['_request' => Collection::class])
        ->assertStatus(400);
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

test('can update cart with customer already in cart and ensure a different email switches the customer', function () {
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

    expect($cart->customer()->id())->not->toBe($customer->id);
    expect($cart->customer()->email())->toBe('dan@smith.test');
    expect(Customer::find($customer->id)->email())->toBe('dan.smith@example.com');
});

test('can update cart and change the email of the logged in users customer', function () {
    setupUserCustomerRepository();

    $user = User::make()->email('james@example.com');
    $user->save();

    $cart = Order::make()->customer($user->id());
    $cart->save();

    $this
        ->actingAs($user)
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart.update'), ['email' => 'james@example.org'])
        ->assertRedirect('/cart');

    expect($cart->fresh()->customer()->id())->toBe($user->id());
    expect(User::find($user->id())->email())->toBe('james@example.org');

    tearDownUserCustomerRepository();
});

test('cant update cart and change the email of the logged in users customer to another users email', function () {
    setupUserCustomerRepository();

    $admin = User::make()->email('admin@example.com')->makeSuper();
    $admin->save();

    $user = User::make()->email('james@example.com');
    $user->save();

    $cart = Order::make()->customer($user->id());
    $cart->save();

    $this
        ->actingAs($user)
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart.update'), ['email' => 'admin@example.com'])
        ->assertRedirect('/cart');

    expect(User::find($admin->id())->email())->toBe('admin@example.com');
    expect(User::find($admin->id())->isSuper())->toBeTrue();
    expect(User::find($user->id())->email())->toBe('james@example.com');
    expect($cart->fresh()->customer()->id())->toBe($admin->id());

    tearDownUserCustomerRepository();
});

test('cant update cart and change the email of another users customer', function () {
    setupUserCustomerRepository();

    $admin = User::make()->email('admin@example.com')->makeSuper();
    $admin->save();

    $cart = Order::make();
    $cart->save();

    $this
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart.update'), ['email' => 'admin@example.com']);

    $this
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart.update'), ['email' => 'attacker@example.com']);

    expect(User::find($admin->id())->email())->toBe('admin@example.com');
    expect($cart->fresh()->customer()->email())->toBe('attacker@example.com');

    tearDownUserCustomerRepository();
});

test('cart association does not grant access to an existing customer profile', function (bool $users, string $input, bool $loggedIn) {
    if ($users) {
        setupUserCustomerRepository();
    }

    try {
        Config::set('simple-commerce.field_whitelist.customers', ['name', 'email', 'dob']);

        $customer = Customer::make()->email('victim@example.com')->data([
            'name' => 'Original Name',
            'dob' => '1st January 1980',
        ])->save();

        if ($loggedIn) {
            $user = User::make()->email('someone-else@example.com');
            $user->save();
            $this->actingAs($user);
        }

        $cart = Order::make()->save();

        $data = match ($input) {
            'email' => ['email' => $customer->email(), 'name' => 'Changed Name'],
            'customer email' => ['customer' => ['email' => $customer->email(), 'name' => 'Changed Name', 'dob' => '2nd February 1990']],
            'customer id' => ['customer' => $customer->id()],
        };

        $this->withSession(['simple-commerce-cart' => $cart->id()])
            ->postJson(route('statamic.simple-commerce.cart.update'), $data)
            ->assertOk()
            ->assertJsonMissingPath('cart.customer')
            ->assertJsonMissingPath('cart.customer_id');

        expect($cart->fresh()->customer()->id())->toBe($customer->id());

        $this->postJson(route('statamic.simple-commerce.cart.update'), [
            'customer' => ['name' => 'Changed Name', 'dob' => '2nd February 1990'],
            'shipping_name' => 'Order Recipient',
        ])->assertOk();

        expect($customer->fresh()->name())->toBe('Original Name');
        expect($customer->get('dob'))->toBe('1st January 1980');
        expect($cart->fresh()->get('shipping_name'))->toBe('Order Recipient');

        $this->getJson(route('statamic.simple-commerce.cart.index'))
            ->assertOk()
            ->assertJsonMissingPath('data.customer')
            ->assertJsonMissingPath('data.customer_id');

        $this->getJson(route('statamic.simple-commerce.customer.index', $customer->id()))
            ->assertForbidden();

        $this->postJson(route('statamic.simple-commerce.customer.update', $customer->id()), [
            'name' => 'Changed Name', 'email' => 'attacker@example.com',
        ])->assertForbidden();

        expect($customer->fresh()->name())->toBe('Original Name');
        expect($customer->email())->toBe('victim@example.com');
    } finally {
        if ($users) {
            tearDownUserCustomerRepository();
        }
    }
})->with(['entries' => false, 'users' => true])
    ->with(['email', 'customer email', 'customer id'])
    ->with(['guest' => false, 'different user' => true]);

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

    $user = User::make()->email($customer->email());
    $user->save();
    $this->actingAs($user);

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

test('cant update cart and create new customer if email contains path characters', function (string $email) {
    $cart = Order::make();
    $cart->save();

    $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart.update'), ['email' => $email])
        ->assertSessionHasErrors('email');

    expect($cart->fresh()->customer())->toBeNull();
})->with([
    'leading slash' => '/joe@example.com',
    'quoted traversal' => '"x/../../p"@example.com',
    'backslash' => '"x\\y"@example.com',
]);

test('can update cart and existing customer by id', function () {
    $customer = Customer::make()->email('jordan.smith@example.com')->data([
        'name' => 'Jordan Smith',
    ]);

    $customer->save();

    $user = User::make()->email($customer->email());
    $user->save();
    $this->actingAs($user);

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

    $user = User::make()->email($customer->email());
    $user->save();
    $this->actingAs($user);

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
        ->postJson(route('statamic.simple-commerce.cart.update'), $data);

    $response->assertOk()->assertJsonStructure(['cart' => ['customer']]);

    $this->getJson(route('statamic.simple-commerce.cart.index'))
        ->assertOk()->assertJsonStructure(['data' => ['customer']]);

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

    $user = User::make()->email($customer->email());
    $user->save();
    $this->actingAs($user);

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

test('can create a customer from mixed top level and nested fields', function (array $data, string $email, string $name, bool $users) {
    if ($users) {
        setupUserCustomerRepository();
    }

    try {
        Config::set('simple-commerce.field_whitelist.customers', ['name', 'email', 'dob']);

        $cart = Order::make()->save();

        $this->withSession(['simple-commerce-cart' => $cart->id()])
            ->postJson(route('statamic.simple-commerce.cart.update'), $data)
            ->assertOk();

        $customer = Customer::findByEmail($email);

        expect($cart->fresh()->customer()->id())->toBe($customer->id());
        expect($customer->name())->toBe($name);
        expect($customer->get('dob'))->toBe('1st January 1980');
        expect(Customer::all())->toHaveCount(1);
    } finally {
        if ($users) {
            tearDownUserCustomerRepository();
        }
    }
})->with([
    'top level email and nested details' => [
        ['email' => 'mixed@example.com', 'customer' => ['name' => 'Nested Name', 'dob' => '1st January 1980']],
        'mixed@example.com', 'Nested Name',
    ],
    'nested values take precedence' => [
        ['email' => 'top@example.com', 'name' => 'Top Name', 'customer' => ['email' => 'nested@example.com', 'name' => 'Nested Name', 'dob' => '1st January 1980']],
        'nested@example.com', 'Nested Name',
    ],
    'empty nested values do not replace top level details' => [
        ['email' => 'mixed@example.com', 'name' => 'Top Name', 'customer' => ['name' => null, 'dob' => '1st January 1980']],
        'mixed@example.com', 'Top Name',
    ],
])->with(['entries' => false, 'users' => true]);

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

test('cant update cart and create new customer via customer array if email contains path characters', function (string $email) {
    $cart = Order::make();
    $cart->save();

    $this
        ->from('/cart')
        ->withSession(['simple-commerce-cart' => $cart->id])
        ->post(route('statamic.simple-commerce.cart.update'), ['customer' => ['email' => $email]])
        ->assertSessionHasErrors('customer.email');

    expect($cart->fresh()->customer())->toBeNull();
})->with([
    'leading slash' => '/joe@example.com',
    'quoted traversal' => '"x/../../p"@example.com',
    'backslash' => '"x\\y"@example.com',
]);

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
