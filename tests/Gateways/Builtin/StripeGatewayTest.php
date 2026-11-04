<?php

use DuncanMcClean\SimpleCommerce\Contracts\Order as ContractsOrder;
use DuncanMcClean\SimpleCommerce\Exceptions\GatewayCheckoutFailed;
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
use Stripe\ApiRequestor;
use Stripe\Customer as StripeCustomer;
use Stripe\HttpClient\ClientInterface;
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

afterEach(function () {
    ApiRequestor::setHttpClient(null);
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
        new Request,
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
        new Request,
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
        new Request,
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
        new Request,
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
    $order = Order::make()->grandTotal(1234)->merge([
        'stripe' => ['intent' => 'pi_123456789122345'],
    ]);

    $order->save();

    fakeStripeApi([
        '/v1/payment_intents/pi_123456789122345' => stripePaymentIntent(),
        '/v1/payment_methods/pm_123' => stripePaymentMethod(),
    ]);

    $gateway = new StripeGateway(['secret' => 'sk_test_123', 'mode' => 'card_elements']);

    $checkout = $gateway->checkout(new Request(['payment_method' => 'pm_123']), $order);

    expect($checkout['id'])->toBe('pm_123');
    expect($checkout['payment_intent'])->toBe('pi_123456789122345');

    $order = $order->fresh();

    expect(PaymentStatus::Paid)->toBe($order->paymentStatus());
    $this->assertNotNull($order->statusLogIncludes(PaymentStatus::Paid));
});

test('cant checkout when in card elements mode and payment is incomplete or does not match order', function (array $paymentIntent) {
    $order = Order::make()->grandTotal(1234)->merge([
        'stripe' => ['intent' => 'pi_123456789122345'],
    ]);

    $order->save();

    fakeStripeApi([
        '/v1/payment_intents/pi_123456789122345' => stripePaymentIntent($paymentIntent),
        '/v1/payment_methods/pm_123' => stripePaymentMethod(),
    ]);

    $gateway = new StripeGateway(['secret' => 'sk_test_123', 'mode' => 'card_elements']);

    expect(fn () => $gateway->checkout(new Request(['payment_method' => 'pm_123']), $order))
        ->toThrow(GatewayCheckoutFailed::class);

    expect(PaymentStatus::Unpaid)->toBe($order->fresh()->paymentStatus());
})->with([
    'not succeeded' => [['status' => 'requires_payment_method', 'amount_received' => 0]],
    'amount less than order total' => [['amount_received' => 100]],
    'different currency' => [['currency' => 'usd']],
]);

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
                'automatic_payment_methods' => ['enabled' => true, 'allow_redirects' => 'never'],
            ])->id,
        ],
    ]);

    $order->save();

    $paymentMethod = PaymentMethod::create([
        'type' => 'card',
        'card' => [
            'number' => '4242424242424242',
            'exp_month' => 7,
            'exp_year' => now()->addYear()->year,
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
    $rules = (new StripeGateway)->checkoutRules();

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
                'automatic_payment_methods' => ['enabled' => true, 'allow_redirects' => 'never'],
            ])->id,
        ]
    );

    $order->save();

    $paymentMethod = PaymentMethod::create([
        'type' => 'card',
        'card' => [
            'number' => '4242424242424242',
            'exp_month' => 7,
            'exp_year' => now()->addYear()->year,
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
    $order = Order::make()->grandTotal(1234);
    $order->save();

    fakeStripeApi(['/v1/events/evt_123' => paymentIntentSucceededEvent($order)]);

    $gateway = new StripeGateway(['secret' => 'sk_test_123']);

    $webhook = $gateway->webhook(stripeWebhookRequest(json_encode(['id' => 'evt_123'])));

    expect($webhook->getStatusCode())->toBe(200);

    $order->fresh();

    expect(OrderStatus::Placed)->toBe($order->status());
    expect(PaymentStatus::Paid)->toBe($order->paymentStatus());

    expect($order->gatewayData()->toArray())->toBe([
        'use' => StripeGateway::handle(),
        'data' => [
            'id' => 'pi_123456789122345',
        ],
        'refund' => null,
    ]);
});

test('cant hit webhook with payment intent succeeded event when amount paid does not match order', function (array $paymentIntent) {
    $order = Order::make()->grandTotal(50000);
    $order->save();

    fakeStripeApi(['/v1/events/evt_123' => paymentIntentSucceededEvent($order, $paymentIntent)]);

    $gateway = new StripeGateway(['secret' => 'sk_test_123']);

    $webhook = $gateway->webhook(stripeWebhookRequest(json_encode(['id' => 'evt_123'])));

    expect($webhook->getStatusCode())->toBe(400);
    expect(PaymentStatus::Unpaid)->toBe($order->fresh()->paymentStatus());
})->with([
    'amount less than order total' => [['amount_received' => 1234]],
    'different currency' => [['amount_received' => 50000, 'currency' => 'usd']],
]);

test('cant hit webhook with event unknown to stripe', function () {
    $order = Order::make();
    $order->save();

    fakeStripeApi();

    $gateway = new StripeGateway(['secret' => 'sk_test_123']);

    $payload = json_encode(paymentIntentSucceededEvent($order));

    $webhook = $gateway->webhook(stripeWebhookRequest($payload));

    expect($webhook->getStatusCode())->toBe(400);
    expect(PaymentStatus::Unpaid)->toBe($order->fresh()->paymentStatus());
});

test('returns array from payment display', function () {
    Stripe::setApiKey(env('STRIPE_SECRET'));

    $fieldtypeDisplay = $this->cardElementsGateway->fieldtypeDisplay([
        'use' => StripeGateway::handle(),
        'data' => [
            'payment_intent' => $paymentIntent = PaymentIntent::create([
                'amount' => 1234,
                'currency' => 'GBP',
                'automatic_payment_methods' => ['enabled' => true, 'allow_redirects' => 'never'],
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

function paymentIntentSucceededEvent(ContractsOrder $order, array $paymentIntent = []): array
{
    return [
        'id' => 'evt_123',
        'object' => 'event',
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => stripePaymentIntent(array_merge([
                'amount_received' => $order->grandTotal(),
                'metadata' => ['order_id' => $order->id()],
            ], $paymentIntent)),
        ],
    ];
}

function stripePaymentIntent(array $attributes = []): array
{
    return array_merge([
        'id' => 'pi_123456789122345',
        'object' => 'payment_intent',
        'status' => 'succeeded',
        'amount' => 1234,
        'amount_received' => 1234,
        'currency' => 'gbp',
        'metadata' => [],
    ], $attributes);
}

function stripePaymentMethod(): array
{
    return [
        'id' => 'pm_123',
        'object' => 'payment_method',
        'card' => ['brand' => 'visa', 'last4' => '4242'],
        'customer' => null,
        'livemode' => false,
    ];
}

function stripeWebhookRequest(string $payload): Request
{
    return new Request([], [], [], [], [], [], $payload);
}

function fakeStripeApi(array $responses = []): void
{
    ApiRequestor::setHttpClient(new class($responses) implements ClientInterface
    {
        public function __construct(private array $responses) {}

        public function request($method, $absUrl, $headers, $params, $hasFile)
        {
            $path = parse_url($absUrl, PHP_URL_PATH);

            if (isset($this->responses[$path])) {
                return [json_encode($this->responses[$path]), 200, []];
            }

            return [json_encode(['error' => ['type' => 'invalid_request_error', 'message' => 'No such resource']]), 404, []];
        }
    });
}
