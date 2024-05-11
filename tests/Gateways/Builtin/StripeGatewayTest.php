<?php

use DuncanMcClean\SimpleCommerce\Contracts\Order as ContractsOrder;
use DuncanMcClean\SimpleCommerce\Exceptions\GatewayHasNotImplementedMethod;
use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Gateways\Builtin\StripeGateway;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\RefreshContent;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Customer as StripeCustomer;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Stripe;

uses(SetupCollections::class);
uses(RefreshContent::class);

beforeEach(function () {
    $this->setupCollections();

    $this->cardElementsGateway = new StripeGateway([
        'secret' => env('STRIPE_SECRET'),
        'mode' => 'card_elements',
    ]);

    $this->paymentElementsGateway = new StripeGateway([
        'secret' => env('STRIPE_SECRET'),
        'mode' => 'payment_elements',
    ]);
});

test('has a name', function () {
    $name = $this->cardElementsGateway->name();

    expect($name)->toBeString();
    expect($name)->toBe('Stripe');
});

test('can prepare', function () {
    $product = Product::make()
        ->price(5500)
        ->data([
            'title' => 'Concert Ticket',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => app('stache')->generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5500,
            'metadata' => [],
        ],
    ])->grandTotal(5500)->merge([
        'title' => '#0001',
    ]);

    $order->save();

    $prepare = $this->cardElementsGateway->prepare(
        new Request(),
        $order
    );

    expect($prepare)->toBeArray();

    $this->assertArrayHasKey('intent', $prepare);
    $this->assertArrayHasKey('client_secret', $prepare);

    $paymentIntent = PaymentIntent::retrieve($prepare['intent']);

    expect($prepare['intent'])->toBe($paymentIntent->id);
    expect($order->grandTotal())->toBe($paymentIntent->amount);
    expect($paymentIntent->customer)->toBeNull();
    expect($paymentIntent->receipt_email)->toBeNull();
})->skip(! env('STRIPE_SECRET'));

test('can prepare with customer', function () {
    $product = Product::make()
        ->price(1299)
        ->data([
            'title' => 'Concert Ticket',
        ]);

    $product->save();

    $customer = Customer::make()->email('george@example.com')->data(['name' => 'George']);
    $customer->save();

    $order = Order::make()->lineItems([
        [
            'id' => app('stache')->generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 1299,
            'metadata' => [],
        ],
    ])->grandTotal(1299)->customer($customer->id())->merge([
        'title' => '#0002',
    ]);

    $order->save();

    $prepare = $this->cardElementsGateway->prepare(
        new Request(),
        $order
    );

    expect($prepare)->toBeArray();

    $this->assertArrayHasKey('intent', $prepare);
    $this->assertArrayHasKey('client_secret', $prepare);

    $paymentIntent = PaymentIntent::retrieve($prepare['intent']);

    expect($prepare['intent'])->toBe($paymentIntent->id);
    expect($order->grandTotal())->toBe($paymentIntent->amount);
    $this->assertNotNull($paymentIntent->customer);
    expect($paymentIntent->receipt_email)->toBeNull();

    $stripeCustomer = StripeCustomer::retrieve($paymentIntent->customer);

    expect($paymentIntent->customer)->toBe($stripeCustomer->id);
    expect('George')->toBe($stripeCustomer->name);
    expect('george@example.com')->toBe($stripeCustomer->email);
})->skip(! env('STRIPE_SECRET'));

test('can prepare with receipt email', function () {
    $product = Product::make()
        ->price(1299)
        ->data([
            'title' => 'Talent Show Ticket',
        ]);

    $product->save();

    $customer = Customer::make()->email('george@example.com')->data(['name' => 'George']);
    $customer->save();

    $this->cardElementsGateway->setConfig([
        'secret' => env('STRIPE_SECRET'),
        'receipt_email' => true,
    ]);

    $order = Order::make()->lineItems([
        [
            'id' => app('stache')->generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 1299,
            'metadata' => [],
        ],
    ])->grandTotal(1299)->customer($customer->id())->merge([
        'title' => '#0003',
    ]);

    $order->save();

    $prepare = $this->cardElementsGateway->prepare(
        new Request(),
        $order
    );

    expect($prepare)->toBeArray();

    $this->assertArrayHasKey('intent', $prepare);
    $this->assertArrayHasKey('client_secret', $prepare);

    $paymentIntent = PaymentIntent::retrieve($prepare['intent']);

    expect($prepare['intent'])->toBe($paymentIntent->id);
    expect($order->grandTotal())->toBe($paymentIntent->amount);
    $this->assertNotNull($paymentIntent->customer);
    expect($customer->email())->toBe($paymentIntent->receipt_email);

    $stripeCustomer = StripeCustomer::retrieve($paymentIntent->customer);

    expect($paymentIntent->customer)->toBe($stripeCustomer->id);
    expect('George')->toBe($stripeCustomer->name);
    expect('george@example.com')->toBe($stripeCustomer->email);
})->skip(! env('STRIPE_SECRET'));

test('can prepare with payment intent data closure', function () {
    $this->cardElementsGateway->setConfig([
        'secret' => env('STRIPE_SECRET'),
        'payment_intent_data' => function (ContractsOrder $order) {
            return [
                'description' => 'Some custom description',
                'metadata' => [
                    'foo' => 'bar',
                ],
            ];
        },
    ]);

    $product = Product::make()
        ->price(1299)
        ->data([
            'title' => 'Concert Ticket',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => app('stache')->generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 1299,
            'metadata' => [],
        ],
    ])->grandTotal(1299)->merge([
        'title' => '#0002',
    ]);

    $order->save();

    $prepare = $this->cardElementsGateway->prepare(
        new Request(),
        $order
    );

    expect($prepare)->toBeArray();

    $this->assertArrayHasKey('intent', $prepare);
    $this->assertArrayHasKey('client_secret', $prepare);

    $paymentIntent = PaymentIntent::retrieve($prepare['intent']);

    expect($prepare['intent'])->toBe($paymentIntent->id);
    expect($order->get('grand_total'))->toBe($paymentIntent->amount);
    expect('Some custom description')->toBe($paymentIntent->description);
    expect('bar')->toBe($paymentIntent->metadata->foo);
    expect($paymentIntent->customer)->toBeNull();
    expect($paymentIntent->receipt_email)->toBeNull();

    $this->cardElementsGateway->setConfig([
        'secret' => env('STRIPE_SECRET'),
    ]);
})->skip(! env('STRIPE_SECRET'));

test('can checkout when in card elements mode', function () {
    Stripe::setApiKey(env('STRIPE_SECRET'));

    $product = Product::make()
        ->price(1234)
        ->data([
            'title' => 'Zoo Ticket',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => app('stache')->generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 1234,
            'metadata' => [],
        ],
    ])->grandTotal(1234)->merge([
        'title' => '#0004',
        'stripe' => [
            'intent' => $paymentIntent = PaymentIntent::create([
                'amount' => 1234,
                'currency' => 'GBP',
            ])->id,
        ],
    ]);

    $order->save();

    $paymentMethod = PaymentMethod::create([
        'type' => 'card',
        'card' => [
            'number' => '4242424242424242',
            'exp_month' => 7,
            'exp_year' => 2024,
            'cvc' => '314',
        ],
    ]);

    PaymentIntent::retrieve($paymentIntent)->confirm([
        'payment_method' => $paymentMethod->id,
    ]);

    $request = new Request(['payment_method' => $paymentMethod->id]);

    $checkout = $this->cardElementsGateway->checkout($request, $order);

    expect($checkout)->toBeArray();

    expect($paymentMethod->id)->toBe($checkout['id']);
    expect($paymentMethod->object)->toBe($checkout['object']);
    expect($paymentMethod->customer)->toBe($checkout['customer']);
    expect($paymentMethod->livemode)->toBe($checkout['livemode']);
    expect($paymentIntent)->toBe($checkout['payment_intent']);

    $order = $order->fresh();

    expect(PaymentStatus::Paid)->toBe($order->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));
})->skip(! env('STRIPE_SECRET'));

test('cant checkout when in payment elements mode', function () {
    Stripe::setApiKey(env('STRIPE_SECRET'));

    $product = Product::make()
        ->price(1234)
        ->data([
            'title' => 'Zoo Ticket',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => app('stache')->generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 1234,
            'metadata' => [],
        ],
    ])->grandTotal(1234)->merge([
        'title' => '#0004',
        'stripe' => [
            'intent' => $paymentIntent = PaymentIntent::create([
                'amount' => 1234,
                'currency' => 'GBP',
            ])->id,
        ],
    ]);

    $order->save();

    $paymentMethod = PaymentMethod::create([
        'type' => 'card',
        'card' => [
            'number' => '4242424242424242',
            'exp_month' => 7,
            'exp_year' => 2024,
            'cvc' => '314',
        ],
    ]);

    PaymentIntent::retrieve($paymentIntent)->confirm([
        'payment_method' => $paymentMethod->id,
    ]);

    $request = new Request(['payment_method' => $paymentMethod->id]);

    $this->expectException(GatewayHasNotImplementedMethod::class);

    $checkout = $this->paymentElementsGateway->checkout($request, $order);

    $order = $order->fresh();

    expect(PaymentStatus::Unpaid)->toBe($order->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));
})->skip(! env('STRIPE_SECRET'));

test('has checkout rules', function () {
    $rules = (new StripeGateway())->checkoutRules();

    expect($rules)->toBeArray();

    $this->assertSame([
        'payment_method' => ['required', 'string'],
    ], $rules);
});

test('can refund charge', function () {
    Stripe::setApiKey(env('STRIPE_SECRET'));

    $order = Order::make()->grandTotal(1234);

    $order->gatewayData(
        gateway: StripeGateway::handle(),
        data: [
            'payment_intent' => $paymentIntent = PaymentIntent::create([
                'amount' => 1234,
                'currency' => 'GBP',
            ])->id,
        ]
    );

    $order->save();

    $paymentMethod = PaymentMethod::create([
        'type' => 'card',
        'card' => [
            'number' => '4242424242424242',
            'exp_month' => 7,
            'exp_year' => 2024,
            'cvc' => '314',
        ],
    ]);

    PaymentIntent::retrieve($paymentIntent)->confirm([
        'payment_method' => $paymentMethod->id,
    ]);

    $refund = $this->cardElementsGateway->refund($order);

    expect($refund)->toBeArray();

    expect($refund['id'])->toContain('re_');
    expect(1234)->toBe($refund['amount']);
    expect($paymentIntent)->toBe($refund['payment_intent']);
})->skip(! env('STRIPE_SECRET'));

test('can hit webhook with payment intent succeeded event', function () {
    $order = Order::make();
    $order->save();

    $payload = [
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => $paymentIntent = 'pi_123456789122345',
                'metadata' => [
                    'order_id' => $order->id(),
                ],
            ],
        ],
    ];

    $webhook = $this->paymentElementsGateway->webhook(new Request([], [], [], [], [], [], json_encode($payload)));

    expect($webhook instanceof Response)->toBeTrue();

    $order->fresh();

    expect(OrderStatus::Placed)->toBe($order->status());
    expect(PaymentStatus::Paid)->toBe($order->paymentStatus());

    expect($order->gatewayData()->toArray())->toBe([
        'use' => StripeGateway::handle(),
        'data' => [
            'id' => $paymentIntent,
        ],
        'refund' => null,
    ]);
});

test('returns array from payment display', function () {
    Stripe::setApiKey(env('STRIPE_SECRET'));

    $fieldtypeDisplay = $this->cardElementsGateway->fieldtypeDisplay([
        'use' => StripeGateway::handle(),
        'data' => [
            'payment_intent' => $paymentIntent = PaymentIntent::create([
                'amount' => 1234,
                'currency' => 'GBP',
            ])->id,
        ],
    ]);

    expect($fieldtypeDisplay)->toBeArray();

    $this->assertSame([
        'text' => $paymentIntent,
        'url' => 'https://dashboard.stripe.com/test/payments/'.$paymentIntent,
    ], $fieldtypeDisplay);
})->skip(! env('STRIPE_SECRET'));

test('does not return array from payment display if no payment intent is set', function () {
    $fieldtypeDisplay = $this->cardElementsGateway->fieldtypeDisplay([
        'use' => StripeGateway::handle(),
        'data' => [],
    ]);

    expect($fieldtypeDisplay)->toBeArray();

    $this->assertSame([
        'text' => 'Unknown',
        'url' => null,
    ], $fieldtypeDisplay);
});
