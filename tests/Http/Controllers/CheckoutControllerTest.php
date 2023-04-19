<?php

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;
use DoubleThreeDigital\SimpleCommerce\Events\OrderStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\PaymentStatusUpdated;
use DoubleThreeDigital\SimpleCommerce\Events\PostCheckout;
use DoubleThreeDigital\SimpleCommerce\Events\PreCheckout;
use DoubleThreeDigital\SimpleCommerce\Events\StockRunningLow;
use DoubleThreeDigital\SimpleCommerce\Events\StockRunOut;
use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayCheckoutFailed;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway;
use DoubleThreeDigital\SimpleCommerce\Gateways\Builtin\DummyGateway;
use DoubleThreeDigital\SimpleCommerce\Notifications\BackOfficeOrderPaid;
use DoubleThreeDigital\SimpleCommerce\Notifications\CustomerOrderPaid;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\RefreshContent;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Statamic\Facades\Stache;

uses(TestCase::class);
uses(SetupCollections::class);
uses(RefreshContent::class);
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
            'gateway' => DummyGateway::class,
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ])
        ->assertSessionHasErrors('accept_terms');

    $this->assertEquals(session('errors')->default->first('accept_terms'), 'Please accept the terms & conditions.');

    $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertNotDispatched(PostCheckout::class);

    $this->assertNotSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertNotSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNull($order->statusLog('paid'));

    // Finally, assert order is no longer attached to the users' session
    $this->assertTrue(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert customer has been created with provided details
    $this->assertNotNull($order->customer());

    $this->assertSame($order->customer()->name(), 'Mike Scott');
    $this->assertSame($order->customer()->email(), 'mike.scott@example.com');

    $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
        $order->id,
    ]);

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert customer has been created with provided details
    $this->assertNotNull($order->customer());

    $this->assertSame($order->customer()->name(), 'Mike Scott');
    $this->assertSame($order->customer()->email(), 'mike.scott@example.com');

    $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
        $order->id,
    ]);

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
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
    $this->assertNull($order->statusLog('paid'));

    // Assert customer has been created with provided details
    $this->assertNull($order->customer());

    // Finally, assert order is no longer attached to the users' session
    $this->assertTrue(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert email has been set on the order
    $this->assertNotNull($order->customer());
    $this->assertNull($order->get('email'));

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert customer has been updated
    $this->assertNotNull($order->customer());
    $this->assertSame($order->customer(), $customer->id);

    $this->assertSame($order->customer()->name(), 'Dwight Schrute');
    $this->assertSame($order->customer()->email(), 'dwight.schrute@example.com');

    $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
        $order->id,
    ]);

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert customer has been updated
    $this->assertNotNull($order->customer());
    $this->assertSame($order->customer()->id(), $customer->id);

    $this->assertSame($order->customer()->name(), 'Stanley Hudson');
    $this->assertSame($order->customer()->email(), 'stanley.hudson@example.com');

    $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
        $order->id,
    ]);

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert customer has been updated
    $this->assertNotNull($order->customer());
    $this->assertSame($order->customer()->id(), $customer->id);

    $this->assertSame($order->customer()->name(), 'Stanley Hudson');
    $this->assertSame($order->customer()->email(), 'stanley.hudson@example.com');

    $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
        $previousOrder->id(),
        $order->id(),
    ]);

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert customer has been created with provided details
    $this->assertNotNull($order->customer());

    $this->assertSame($order->customer()->name(), 'Joe Doe');
    $this->assertSame($order->customer()->email(), 'joe.doe@example.com');

    $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
        $order->id,
    ]);

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert customer has been created with provided details
    $this->assertNotNull($order->customer());

    $this->assertSame($order->customer()->id(), $customer->id);
    $this->assertSame($order->customer()->name(), 'Joe Doe');
    $this->assertSame($order->customer()->email(), 'joe.doe@example.com');

    $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
        $order->id,
    ]);

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
             'gateway' => DummyGateway::class,
             'card_number' => '4242424242424242',
             'expiry_month' => '01',
             'expiry_year' => '2025',
             'cvc' => '123',
         ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert customer has been created with provided details
    $this->assertNotNull($order->customer());

    $this->assertSame($order->customer()->name(), 'Joe Doe');
    $this->assertSame($order->customer()->email(), 'joe.doe@example.com');
    $this->assertSame($order->customer()->get('dob'), '01/01/2000');

    $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
        $order->id,
    ]);

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
             'gateway' => DummyGateway::class,
             'card_number' => '4242424242424242',
             'expiry_month' => '01',
             'expiry_year' => '2025',
             'cvc' => '123',
         ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert customer has been created with provided details
    $this->assertNotNull($order->customer());

    $this->assertSame($order->customer()->id(), $customer->id);
    $this->assertSame($order->customer()->name(), 'Joe Doe');
    $this->assertSame($order->customer()->email(), 'joe.doe@example.com');
    $this->assertSame($order->customer()->get('dob'), '01/01/2000');

    $this->assertSame($order->customer()->orders()->pluck('id')->unique()->toArray(), [
        $order->id,
    ]);

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
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

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert the coupon has been redeemed propery & the total has been recalculated
    $this->assertSame($order->coupon()->id(), $coupon->id);

    $this->assertSame($order->grandTotal(), 2500);
    $this->assertSame($order->couponTotal(), 2500);

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert the coupon has been redeemed propery & the total has been recalculated
    $this->assertSame($order->coupon()->id(), $coupon->id);

    $this->assertSame($order->grandTotal(), 0);
    $this->assertSame($order->couponTotal(), 5000);

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
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
    $this->assertNull($order->statusLog('paid'));

    // Assert the coupon has been redeemed propery & the total has been recalculated
    $this->assertNull($order->coupon());

    $this->assertSame($order->grandTotal(), 5000);
    $this->assertSame($order->couponTotal(), 0);

    // Finally, assert order is no longer attached to the users' session
    $this->assertTrue(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
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
    $this->assertNull($order->statusLog('paid'));

    // Assert the coupon has been redeemed propery & the total has been recalculated
    $this->assertNull($order->coupon());

    $this->assertSame($order->grandTotal(), 5000);
    $this->assertSame($order->couponTotal(), 0);

    // Finally, assert order is no longer attached to the users' session
    $this->assertTrue(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
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
    $this->assertNull($order->statusLog('paid'));

    // Assert the coupon has been redeemed propery & the total has been recalculated
    $this->assertNull($order->coupon());

    $this->assertSame($order->grandTotal(), 5000);
    $this->assertSame($order->couponTotal(), 0);

    // Finally, assert order is no longer attached to the users' session
    $this->assertTrue(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert stock has been reduced
    $this->assertSame($product->fresh()->stock(), 49);

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert stock has been reduced
    $this->assertSame($product->fresh()->stock(), 8);

    Event::assertDispatched(StockRunningLow::class);

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors();

    $order = $order->fresh();

    // Assert the line item has been wiped out
    $this->assertSame($order->lineItems()->count(), 0);
    $this->assertSame($order->grandTotal(), 0);

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertNotDispatched(PostCheckout::class);

    $this->assertNotSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertNotSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNull($order->statusLog('paid'));

    // Asset the stock is the same (it hasn't been reduced yet because the
    // checkout failed at the validation stage)
    $product->fresh();

    $this->assertSame($product->stock(), 0);

    // Finally, assert order is no longer attached to the users' session
    $this->assertTrue(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
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

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert stock has been reduced
    $this->assertSame($product->fresh()->stock(), 0);

    Event::assertDispatched(StockRunningLow::class);
    Event::assertDispatched(StockRunOut::class);

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert stock has been reduced
    $this->assertSame($product->fresh()->variant('Red_Small')->stock(), 49);

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert stock has been reduced
    $this->assertSame($product->fresh()->variant('Red_Small')->stock(), 8);

    Event::assertDispatched(StockRunningLow::class);

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors();

    $order = $order->fresh();

    // Assert the line item has been wiped out
    $this->assertSame($order->lineItems()->count(), 0);
    $this->assertSame($order->grandTotal(), 0);

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertNotDispatched(PostCheckout::class);

    $this->assertNotSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertNotSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNull($order->statusLog('paid'));

    // Asset the stock is the same (it hasn't been reduced yet because the
    // checkout failed at the validation stage)
    $this->assertSame($product->fresh()->variant('Red_Small')->stock(), 0);

    // Finally, assert order is no longer attached to the users' session
    $this->assertTrue(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertDispatched(PostCheckout::class);

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert stock has been reduced
    $this->assertSame($product->fresh()->variant('Red_Small')->stock(), 0);

    Event::assertDispatched(StockRunningLow::class);
    Event::assertDispatched(StockRunOut::class);

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
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

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert that the 'extra remaining data' has been saved to the order
    $this->assertSame($order->get('gift_note'), 'I like jam on toast!');
    $this->assertSame($order->get('delivery_note'), 'We live at the red house at the top of the hill.');

    $this->assertSame($order->get('the_extra'), 'bit_of_data');

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
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

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert that the 'extra remaining data' has been saved to the order
    $this->assertSame($order->get('gift_note'), 'I like jam on toast!');
    $this->assertSame($order->get('delivery_note'), 'We live at the red house at the top of the hill.');

    $this->assertNull($order->get('the_extra'));

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
        ])
        ->assertSessionHasErrors(['card_number', 'expiry_month', 'expiry_year', 'cvc']);

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertNotDispatched(PostCheckout::class);

    $this->assertNotSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertNotSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNull($order->statusLog('paid'));

    // Finally, assert order is no longer attached to the users' session
    $this->assertTrue(session()->has('simple-commerce-cart'));
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
    $this->assertNull($order->statusLog('paid'));

    // Finally, assert order is no longer attached to the users' session
    $this->assertTrue(session()->has('simple-commerce-cart'));
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
    $this->assertNull($order->statusLog('paid'));

    // Finally, assert order is no longer attached to the users' session
    $this->assertTrue(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
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

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert customer has been created with provided details
    $this->assertNotNull($order->customer());

    $this->assertSame($order->customer()->name(), 'Smelly Joe');
    $this->assertSame($order->customer()->email(), 'smelly.joe@example.com');

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
            'gateway' => DummyGateway::class,
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

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert customer has been created with provided details
    $this->assertNotNull($order->customer());

    $this->assertSame($order->customer()->name(), 'Smelly Joe');
    $this->assertSame($order->customer()->email(), 'smelly.joe@example.com');

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
});

test('can post checkout and ensure order paid notifications are sent', function () {
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
            'gateway' => DummyGateway::class,
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

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Finally, assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));
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
    $this->assertIsArray($order->get('dummy'));

    $this
        ->withSession(['simple-commerce-cart' => $order->id])
        ->post(route('statamic.simple-commerce.checkout.store'), [
            'name' => 'Smelly Joe',
            'email' => 'smelly.joe@example.com',
            'gateway' => DummyGateway::class,
            'card_number' => '4242424242424242',
            'expiry_month' => '01',
            'expiry_year' => '2025',
            'cvc' => '123',
        ]);

    $order = $order->fresh();

    $this->assertSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNotNull($order->statusLog('paid'));

    // Assert order is no longer attached to the users' session
    $this->assertFalse(session()->has('simple-commerce-cart'));

    // Finally, assert 'dummy' gateway temp data has been tiedied up
    $this->assertNull($order->get('dummy'));
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
            'gateway' => TestValidationGateway::class,
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
    Event::assertDispatched(PreCheckout::class);
    Event::assertNotDispatched(PostCheckout::class);

    $this->assertNotSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertNotSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNull($order->statusLog('paid'));

    // Finally, assert order is no longer attached to the users' session
    $this->assertTrue(session()->has('simple-commerce-cart'));
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
            'gateway' => TestCheckoutErrorGateway::class,
        ])
        ->assertSessionHasErrors('gateway');

    $order = $order->fresh();

    // Assert events have been dispatched
    Event::assertDispatched(PreCheckout::class);
    Event::assertNotDispatched(PostCheckout::class);

    $this->assertNotSame($order->fresh()->status(), OrderStatus::Placed);
    $this->assertNotSame($order->fresh()->paymentStatus(), PaymentStatus::Paid);
    $this->assertNull($order->statusLog('paid'));

    // Finally, assert order is no longer attached to the users' session
    $this->assertTrue(session()->has('simple-commerce-cart'));
});

// Helpers
function authorize()
{
    return true;
}

function rules()
{
    return [
        'accept_terms' => ['required', 'boolean'],
    ];
}

function messages()
{
    return [
        'accept_terms.required' => 'Please accept the terms & conditions.',
    ];
}

function name(): string
{
    return 'Test Checkout Error Gateway';
}

function isOffsiteGateway(): bool
{
    return false;
}

function prepare(Request $request, OrderContract $order): array
{
    return [
        'bagpipes' => 'music',
        'checkout_url' => 'http://backpipes.com',
    ];
}

function checkout(Request $request, OrderContract $order): array
{
    throw new GatewayCheckoutFailed('Something went wrong with your payment. Sorry!');
}

function checkoutRules(): array
{
    return [];
}

function checkoutMessages(): array
{
    return [];
}

function refund(OrderContract $order): array
{
    return [];
}

function webhook(Request $request)
{
    return 'Success.';
}
