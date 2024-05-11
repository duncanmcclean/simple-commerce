<?php

use DuncanMcClean\SimpleCommerce\Events\OrderStatusUpdated;
use DuncanMcClean\SimpleCommerce\Events\PaymentStatusUpdated;
use DuncanMcClean\SimpleCommerce\Events\PostCheckout;
use DuncanMcClean\SimpleCommerce\Events\PreCheckout;
use DuncanMcClean\SimpleCommerce\Events\StockRunningLow;
use DuncanMcClean\SimpleCommerce\Events\StockRunOut;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Gateways\Builtin\DummyGateway;
use DuncanMcClean\SimpleCommerce\Notifications\BackOfficeOrderPaid;
use DuncanMcClean\SimpleCommerce\Notifications\CustomerOrderPaid;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Gateways\TestCheckoutErrorGateway;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Gateways\TestValidationGateway;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Http\Requests\CheckoutFormRequest;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\RefreshContent;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Statamic\Facades\Stache;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

uses(SetupCollections::class);
uses(RefreshContent::class);
uses(PreventsSavingStacheItemsToDisk::class);

beforeEach(function () {
    $this->useBasicTaxEngine();
});

test('can post checkout', function () {
    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('cant post checkout and ensure custom form request is used', function () {
    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            '_request' => encrypt(CheckoutFormRequest::class),
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ])
        ->assertSessionHasErrors('accept_terms');

    expect('Please accept the terms & conditions.')->toEqual(session('errors')->default->first('accept_terms'));

    $order->fresh();

    // Assert events have been dispatched
    Event::assertNotDispatched(PreCheckout::class);
    Event::assertNotDispatched(PostCheckout::class);

    $this->assertNotSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertNotSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    expect($order->statusLogIncludes(PaymentStatus::Paid))->toBeFalse();

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeTrue();
});

test('can post checkout with name and email', function () {
    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Mike Scott',
            'email' => 'mike.scott@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert customer has been created with provided details
    $this->assertNotNull($order->customer());

    expect('Mike Scott')->toBe($order->customer()->name());
    expect('mike.scott@example.com')->toBe($order->customer()->email());

    $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
        $order->id,
    ]);

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('can post checkout with first name and last name and email', function () {
    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'first_name' => 'Mike',
            'last_name' => 'Scott',
            'email' => 'mike.scott@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert customer has been created with provided details
    $this->assertNotNull($order->customer());

    expect('Mike Scott')->toBe($order->customer()->name());
    expect('mike.scott@example.com')->toBe($order->customer()->email());

    $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
        $order->id,
    ]);

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('cant post checkout with name and email when email address contains spaces', function () {
    Event::fake();

    $product = Product::make()->price(5000)->data([
        'title' => 'Bacon',
    ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Mike Scott',
            'email' => 'mike dot scott@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ])
        ->assertSessionHasErrors('email');

    $order->fresh();

    // Assert events have been dispatched
    Event::assertNotDispatched(PreCheckout::class);
    Event::assertNotDispatched(PostCheckout::class);

    $this->assertNotSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertNotSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    expect($order->statusLogIncludes(PaymentStatus::Paid))->toBeFalse();

    // Assert customer has been created with provided details
    expect($order->customer())->toBeNull();

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeTrue();
});

test('can post checkout with only email', function () {
    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'email' => 'jim@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert email has been set on the order
    $this->assertNotNull($order->customer());
    expect($order->get('email'))->toBeNull();

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('can post checkout with customer already present in order', function () {
    $this->markTestSkipped();

    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $customer = Customer::make()->email('dwight.schrute@example.com')->data([
        'name' => 'Dwight Schrute',
    ]);

    $customer->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000)->merge([
        'customer' => $customer->id,
    ]);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert customer has been updated
    $this->assertNotNull($order->customer());
    expect($customer->id)->toBe($order->customer());

    expect('Dwight Schrute')->toBe($order->customer()->name());
    expect('dwight.schrute@example.com')->toBe($order->customer()->email());

    $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
        $order->id,
    ]);

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('can post checkout with customer present in request', function () {
    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $customer = Customer::make()->email('stanley.hudson@example.com')->data([
        'name' => 'Stanley Hudson',
    ]);

    $customer->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'customer' => $customer->id,
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert customer has been updated
    $this->assertNotNull($order->customer());
    expect($customer->id)->toBe($order->customer()->id());

    expect('Stanley Hudson')->toBe($order->customer()->name());
    expect('stanley.hudson@example.com')->toBe($order->customer()->email());

    $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
        $order->id,
    ]);

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

/**
 * https://github.com/duncanmcclean/simple-commerce/issues/629
 */
test('can post checkout with customer where customer has invalid orders', function () {
    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $previousOrder = Order::make();
    $previousOrder->save();

    $customer = Customer::make()
        ->email('stanley.hudson@example.com')
        ->data([
            'name' => 'Stanley Hudson',
            'orders' => [
                'abc',
                '123',
                $previousOrder->id(),
            ],
        ]);

    $customer->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'customer' => $customer->id,
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert customer has been updated
    $this->assertNotNull($order->customer());
    expect($customer->id)->toBe($order->customer()->id());

    expect('Stanley Hudson')->toBe($order->customer()->name());
    expect('stanley.hudson@example.com')->toBe($order->customer()->email());

    $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
        $previousOrder->id(),
        $order->id(),
    ]);

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('can post checkout with customer array', function () {
    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'customer' => [
                'name' => 'Joe Doe',
                'email' => 'joe.doe@example.com',
            ],
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert customer has been created with provided details
    $this->assertNotNull($order->customer());

    expect('Joe Doe')->toBe($order->customer()->name());
    expect('joe.doe@example.com')->toBe($order->customer()->email());

    $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
        $order->id,
    ]);

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('can post checkout with customer array and existing customer', function () {
    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $customer = Customer::make()
        ->email('joe.doe@example.com')
        ->data([
            'name' => 'Joe Doe',
        ]);

    $customer->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'customer' => [
                'name' => 'Joe Doe',
                'email' => 'joe.doe@example.com',
            ],
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert customer has been created with provided details
    $this->assertNotNull($order->customer());

    expect($customer->id)->toBe($order->customer()->id());
    expect('Joe Doe')->toBe($order->customer()->name());
    expect('joe.doe@example.com')->toBe($order->customer()->email());

    $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
        $order->id,
    ]);

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

/**
 * https://github.com/duncanmcclean/simple-commerce/issues/658
 */
test('can post checkout with customer array with additional information', function () {
    Config::set('simple-commerce.field_whitelist.customers', [
        'name', 'email', 'dob',
    ]);

    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'customer' => [
                'name' => 'Joe Doe',
                'email' => 'joe.doe@example.com',
                'dob' => '01/01/2000',
            ],
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert customer has been created with provided details
    $this->assertNotNull($order->customer());

    expect('Joe Doe')->toBe($order->customer()->name());
    expect('joe.doe@example.com')->toBe($order->customer()->email());
    expect('01/01/2000')->toBe($order->customer()->get('dob'));

    $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
        $order->id,
    ]);

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

/**
 * https://github.com/duncanmcclean/simple-commerce/issues/658
 */
test('can post checkout with customer array and existing customer with additional information', function () {
    Config::set('simple-commerce.field_whitelist.customers', [
        'name', 'email', 'dob',
    ]);

    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $customer = Customer::make()
        ->email('joe.doe@example.com')
        ->data([
            'name' => 'Joe Doe',
        ]);

    $customer->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'customer' => [
                'name' => 'Joe Doe',
                'email' => 'joe.doe@example.com',
                'dob' => '01/01/2000',
            ],
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert customer has been created with provided details
    $this->assertNotNull($order->customer());

    expect($customer->id)->toBe($order->customer()->id());
    expect('Joe Doe')->toBe($order->customer()->name());
    expect('joe.doe@example.com')->toBe($order->customer()->email());
    expect('01/01/2000')->toBe($order->customer()->get('dob'));

    $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
        $order->id,
    ]);

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('can post checkout with customer array and use logged in user as customer', function () {
    Event::fake();
    setupUserCustomerRepository();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $user = User::make()->email('james@example.com')->set('name', 'James Test');
    $user->save();

    $this
        ->actingAs($user)
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert customer has been created with provided details
    $this->assertNotNull($order->customer());

    expect($user->id)->toBe($order->customer()->id());
    expect($user->name)->toBe($order->customer()->name());
    expect($user->email)->toBe($order->customer()->email());

    $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
        $order->id,
    ]);

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();

    tearDownUserCustomerRepository();
});

test('can post checkout with coupon', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 0);
    Config::set('simple-commerce.sites.default.shipping.methods', []);

    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $coupon = Coupon::make()
        ->code('fifty-friday')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Fifty Friday',
            'redeemed' => 0,
            'minimum_cart_value' => null,
        ]);

    $coupon->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
            'coupon' => 'fifty-friday',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert the coupon has been redeemed propery & the total has been recalculated
    expect($coupon->id)->toBe($order->coupon()->id());

    expect(2500)->toBe($order->grandTotal());
    expect(2500)->toBe($order->couponTotal());

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('can post checkout with coupon when checkout request will reach the coupons maximum uses value', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 0);
    Config::set('simple-commerce.sites.default.shipping.methods', []);

    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $coupon = Coupon::make()
        ->code('full-friday')
        ->value(100)
        ->type('percentage')
        ->data([
            'description' => 'Fifty Friday',
            'redeemed' => 0,
            'minimum_cart_value' => null,
            'maximum_uses' => 1,
        ]);

    $coupon->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(0)->coupon($coupon->id());

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert the coupon has been redeemed propery & the total has been recalculated
    expect($coupon->id)->toBe($order->coupon()->id());

    expect(0)->toBe($order->grandTotal());
    expect(5000)->toBe($order->couponTotal());

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('cant post checkout with coupon where minimum cart value has not been reached', function () {
    $this->markTestSkipped();

    Config::set('simple-commerce.tax_engine_config.rate', 0);
    Config::set('simple-commerce.sites.default.shipping.methods', []);

    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $coupon = Coupon::make()
        ->code('fifty-thursday')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Fifty Thursday',
            'redeemed' => 0,
            'minimum_cart_value' => 9000,
        ]);

    $coupon->save();

    $order = Order::make()
        ->lineItems([
            [
                'id' => Stache::generateId(),
                'product' => $product->id,
                'quantity' => 1,
                'total' => 5000,
            ],
        ])
        ->grandTotal(5000)
        ->itemsTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
            'coupon' => $coupon->code(),
        ])
        ->assertSessionHasErrors('coupon');

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertNotDispatched(PostCheckout::class);

    $this->assertNotSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertNotSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    expect($order->statusLogIncludes(PaymentStatus::Paid))->toBeFalse();

    // Assert the coupon has been redeemed propery & the total has been recalculated
    expect($order->coupon())->toBeNull();

    expect(5000)->toBe($order->grandTotal());
    expect(0)->toBe($order->couponTotal());

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeTrue();
});

test('cant post checkout with coupon when coupon has been redeemed for maxium uses', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 0);
    Config::set('simple-commerce.sites.default.shipping.methods', []);

    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $coupon = Coupon::make()
        ->code('fifty-thursday')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Fifty Thursday',
            'redeemed' => 10,
            'maximum_uses' => 10,
            'minimum_cart_value' => null,
        ]);

    $coupon->save();

    $order = Order::make()
        ->lineItems([
            [
                'id' => Stache::generateId(),
                'product' => $product->id,
                'quantity' => 1,
                'total' => 5000,
            ],
        ])
        ->grandTotal(5000)
        ->itemsTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
            'coupon' => $coupon->code(),
        ])
        ->assertSessionHasErrors('coupon');

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertNotDispatched(PreCheckout::class);
    Event::assertNotDispatched(PostCheckout::class);

    $this->assertNotSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertNotSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    expect($order->statusLogIncludes(PaymentStatus::Paid))->toBeFalse();

    // Assert the coupon has been redeemed propery & the total has been recalculated
    expect($order->coupon())->toBeNull();

    expect(5000)->toBe($order->grandTotal());
    expect(0)->toBe($order->couponTotal());

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeTrue();
});

test('cant post checkout with coupon where coupon is only valid for products not in cart', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 0);
    Config::set('simple-commerce.sites.default.shipping.methods', []);

    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $coupon = Coupon::make()
        ->code('fifty-wednesday')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Fifty Wednesday',
            'redeemed' => 0,
            'minimum_cart_value' => null,
            'products' => ['a-random-product'],
        ]);

    $coupon->save();

    $order = Order::make()
        ->lineItems([
            [
                'id' => Stache::generateId(),
                'product' => $product->id,
                'quantity' => 1,
                'total' => 5000,
            ],
        ])
        ->grandTotal(5000)
        ->itemsTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
            'coupon' => $coupon->code(),
        ])
        ->assertSessionHasErrors('coupon');

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertNotDispatched(PreCheckout::class);
    Event::assertNotDispatched(PostCheckout::class);

    $this->assertNotSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertNotSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    expect($order->statusLogIncludes(PaymentStatus::Paid))->toBeFalse();

    // Assert the coupon has been redeemed propery & the total has been recalculated
    expect($order->coupon())->toBeNull();

    expect(5000)->toBe($order->grandTotal());
    expect(0)->toBe($order->couponTotal());

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeTrue();
});

test('can post checkout with product with stock counter', function () {
    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->stock(50)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert stock has been reduced
    expect(49)->toBe($product->fresh()->stock());

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('can post checkout when product is running low on stock', function () {
    Config::set('simple-commerce.low_stock_threshold', 10);

    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->stock(9)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert stock has been reduced
    expect(8)->toBe($product->fresh()->stock());

    Event::assertDispatched(StockRunningLow::class);

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('cant post checkout when product has no stock', function () {
    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->stock(0)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors();

    $order = $order->fresh();

    // Assert the line item has been wiped out
    expect(0)->toBe($order->lineItems()->count());
    expect(0)->toBe($order->grandTotal());

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertNotDispatched(PostCheckout::class);

    $this->assertNotSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertNotSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    expect($order->statusLogIncludes(PaymentStatus::Paid))->toBeFalse();

    // Asset the stock is the same (it hasn't been reduced yet because the
    // checkout failed at the validation stage)
    $product->fresh();

    expect(0)->toBe($product->stock());

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeTrue();
});

test('cant post checkout when product has a single item left in stock and single quantity in cart', function () {
    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->stock(1)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ])
        ->assertRedirect();

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert stock has been reduced
    expect(0)->toBe($product->fresh()->stock());

    Event::assertDispatched(StockRunningLow::class);
    Event::assertDispatched(StockRunOut::class);

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('can post checkout with variant product with stock counter', function () {
    Event::fake();

    $product = Product::make()
        ->data([
            'title' => 'Bacon',
        ])
        ->productVariants([
            'variants' => [
                [
                    'name' => 'Colours',
                    'values' => [
                        'Red',
                    ],
                ],
                [
                    'name' => 'Sizes',
                    'values' => [
                        'Small',
                    ],
                ],
            ],
            'options' => [
                [
                    'key' => 'Red_Small',
                    'variant' => 'Red Small',
                    'price' => 5000,
                    'stock' => 50,
                ],
            ],
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'variant' => 'Red_Small',
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $r = $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert stock has been reduced
    expect(49)->toBe($product->fresh()->variant('Red_Small')->stock());

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('can post checkout when variant product is running low on stock', function () {
    Config::set('simple-commerce.low_stock_threshold', 10);

    Event::fake();

    $product = Product::make()
        ->data([
            'title' => 'Bacon',
        ])
        ->productVariants([
            'variants' => [
                [
                    'name' => 'Colours',
                    'values' => [
                        'Red',
                    ],
                ],
                [
                    'name' => 'Sizes',
                    'values' => [
                        'Small',
                    ],
                ],
            ],
            'options' => [
                [
                    'key' => 'Red_Small',
                    'variant' => 'Red Small',
                    'price' => 5000,
                    'stock' => 9,
                ],
            ],
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'variant' => 'Red_Small',
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert stock has been reduced
    expect(8)->toBe($product->fresh()->variant('Red_Small')->stock());

    Event::assertDispatched(StockRunningLow::class);

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('cant post checkout when variant product has no stock', function () {
    Event::fake();

    $product = Product::make()
        ->data([
            'title' => 'Bacon',
        ])
        ->productVariants([
            'variants' => [
                [
                    'name' => 'Colours',
                    'values' => [
                        'Red',
                    ],
                ],
                [
                    'name' => 'Sizes',
                    'values' => [
                        'Small',
                    ],
                ],
            ],
            'options' => [
                [
                    'key' => 'Red_Small',
                    'variant' => 'Red Small',
                    'price' => 5000,
                    'stock' => 0,
                ],
            ],
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'variant' => 'Red_Small',
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors();

    $order = $order->fresh();

    // Assert the line item has been wiped out
    expect(0)->toBe($order->lineItems()->count());
    expect(0)->toBe($order->grandTotal());

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertNotDispatched(PostCheckout::class);

    $this->assertNotSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertNotSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    expect($order->statusLogIncludes(PaymentStatus::Paid))->toBeFalse();

    // Asset the stock is the same (it hasn't been reduced yet because the
    // checkout failed at the validation stage)
    expect(0)->toBe($product->fresh()->variant('Red_Small')->stock());

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeTrue();
});

test('can post checkout when variant product has a single item left in stock and single quantity in cart', function () {
    Config::set('simple-commerce.low_stock_threshold', 10);

    Event::fake();

    $product = Product::make()
        ->data([
            'title' => 'Bacon',
        ])
        ->productVariants([
            'variants' => [
                [
                    'name' => 'Colours',
                    'values' => [
                        'Red',
                    ],
                ],
                [
                    'name' => 'Sizes',
                    'values' => [
                        'Small',
                    ],
                ],
            ],
            'options' => [
                [
                    'key' => 'Red_Small',
                    'variant' => 'Red Small',
                    'price' => 5000,
                    'stock' => 1,
                ],
            ],
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'variant' => 'Red_Small',
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert stock has been reduced
    expect(0)->toBe($product->fresh()->variant('Red_Small')->stock());

    Event::assertDispatched(StockRunningLow::class);
    Event::assertDispatched(StockRunOut::class);

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('can post checkout and ensure remaining request data is saved to order', function () {
    Event::fake();

    Config::set('simple-commerce.field_whitelist.orders', ['the_extra']);

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000)->merge([
        'gift_note' => 'I like jam on toast!',
        'delivery_note' => 'We live at the red house at the top of the hill.',
    ]);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
            'the_extra' => 'bit_of_data',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert that the 'extra remaining data' has been saved to the order
    expect('I like jam on toast!')->toBe($order->get('gift_note'));
    expect('We live at the red house at the top of the hill.')->toBe($order->get('delivery_note'));

    expect('bit_of_data')->toBe($order->get('the_extra'));

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('cant post checkout and ensure remaining request data is saved to order if fields not whitelisted in config', function () {
    Event::fake();

    Config::set('simple-commerce.field_whitelist.orders', []);

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000)->merge([
        'gift_note' => 'I like jam on toast!',
        'delivery_note' => 'We live at the red house at the top of the hill.',
    ]);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
            'the_extra' => 'bit_of_data',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert that the 'extra remaining data' has been saved to the order
    expect('I like jam on toast!')->toBe($order->get('gift_note'));
    expect('We live at the red house at the top of the hill.')->toBe($order->get('delivery_note'));

    expect($order->get('the_extra'))->toBeNull();

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('can post checkout with no payment information on free order', function () {
    Event::fake();

    $product = Product::make()
        ->price(0)
        ->data([
            'title' => 'Nothing',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 0,
        ],
    ])->grandTotal(0);

    $order->save();

    $response = $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(OrderStatusUpdated::class);
    Event::assertDispatched(PaymentStatusUpdated::class);
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('cant post checkout with no payment information on paid order', function () {
    $this->markTestIncomplete();

    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => DummyGateway::handle(),
        ])
        ->assertSessionHasErrors(['card_number', 'expiry_month', 'expiry_year', 'cvc']);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertNotDispatched(PostCheckout::class);

    $this->assertNotSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertNotSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    expect($order->statusLogIncludes(PaymentStatus::Paid))->toBeFalse();

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeTrue();
});

test('cant post checkout with no gateway in request', function () {
    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
        ])
        ->assertSessionHasErrors('gateway');

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertNotDispatched(PreCheckout::class);
    Event::assertNotDispatched(PostCheckout::class);

    $this->assertNotSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertNotSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    expect($order->statusLogIncludes(PaymentStatus::Paid))->toBeFalse();

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeTrue();
});

test('cant post checkout with invalid gateway in request', function () {
    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => 'TripleFourDigital\\ComplexCommerce\\SmellyGatewayHaha',
        ])
        ->assertSessionHasErrors('gateway');

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertNotDispatched(PreCheckout::class);
    Event::assertNotDispatched(PostCheckout::class);

    $this->assertNotSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertNotSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    expect($order->statusLogIncludes(PaymentStatus::Paid))->toBeFalse();

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeTrue();
});

test('can post checkout requesting json and ensure json is returned', function () {
    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->postJson(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ])
        ->assertJsonStructure([
            'message',
            'cart',
            'status',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert customer has been created with provided details
    $this->assertNotNull($order->customer());

    expect('Smelly Joe')->toBe($order->customer()->name());
    expect('smelly.joe@example.com')->toBe($order->customer()->email());

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('can post checkout and ensure user is redirected', function () {
    Event::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
            '_redirect' => encrypt('/order-confirmation'),
        ])
        ->assertRedirect('/order-confirmation');

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert customer has been created with provided details
    $this->assertNotNull($order->customer());

    expect('Smelly Joe')->toBe($order->customer()->name());
    expect('smelly.joe@example.com')->toBe($order->customer()->email());

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('can post checkout and ensure order paid notifications are sent', function () {
    config(['simple-commerce.notifications.order_paid' => [
        \DuncanMcClean\SimpleCommerce\Notifications\CustomerOrderPaid::class => [
            'to' => 'customer',
        ],
        \DuncanMcClean\SimpleCommerce\Notifications\BackOfficeOrderPaid::class => [
            'to' => 'duncan@example.com',
        ],
    ]]);

    Notification::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Guvna B',
            'email' => 'guvna.b@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Asset notifications have been sent
    Notification::assertSentTo(
        (new AnonymousNotifiable())->route('mail', 'guvna.b@example.com'),
        CustomerOrderPaid::class
    );

    Notification::assertSentTo(
        (new AnonymousNotifiable())->route('mail', 'duncan@example.com'),
        BackOfficeOrderPaid::class
    );

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();
});

test('can post checkout and ensure temp gateway data is tidied up', function () {
    Notification::fake();

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000)->merge([
        'dummy' => [
            'foo' => 'bar',
        ],
    ]);

    $order->save();

    // Double check 'dummy' temp data is actually present
    expect($order->get('dummy'))->toBeArray();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => DummyGateway::handle(),
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    expect(OrderStatus::Placed)->toBe($order->fresh()->status());
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));

    // Assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeFalse();

    // Finally, assert 'dummy' gateway temp data has been tiedied up
    expect($order->get('dummy'))->toBeNull();
});

test('can post checkout and ensure gateway validation rules are used', function () {
    Event::fake();

    SimpleCommerce::registerGateway(TestValidationGateway::class);

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->postJson(route('statamic.simple-commerce.checkout.store'), [
            'gateway' => TestValidationGateway::handle(),
        ])
        ->assertJson([
            'errors' => [
                'something_mental' => [
                    'You must have something mental to do.',
                ],
            ],
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertNotDispatched(PreCheckout::class);
    Event::assertNotDispatched(PostCheckout::class);

    $this->assertNotSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertNotSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    expect($order->statusLogIncludes(PaymentStatus::Paid))->toBeFalse();

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeTrue();
});

test('can post checkout and ensure gateway errors are handled correctly', function () {
    Event::fake();

    SimpleCommerce::registerGateway(TestCheckoutErrorGateway::class);

    $product = Product::make()
        ->price(5000)
        ->data([
            'title' => 'Bacon',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5000,
        ],
    ])->grandTotal(5000);

    $order->save();

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => TestCheckoutErrorGateway::handle(),
        ])
        ->assertSessionHasErrors('gateway');

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertNotDispatched(PostCheckout::class);

    $this->assertNotSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertNotSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    expect($order->statusLogIncludes(PaymentStatus::Paid))->toBeFalse();

    // Finally, assert order is no longer attached to the users' session
    expect(session()->has('simple-commerce-cart'))->toBeTrue();
});
