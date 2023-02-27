<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Gateways\Builtin;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order as ContractsOrder;
use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayHasNotImplementedMethod;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Gateways\Builtin\StripeGateway;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\RefreshContent;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Customer as StripeCustomer;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Stripe;

class StripeGatewayTest extends TestCase
{
    use SetupCollections, RefreshContent;

    public StripeGateway $cardElementsGateway;

    public StripeGateway $paymentElementsGateway;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupCollections();

        $this->cardElementsGateway = new StripeGateway([
            'secret' => env('STRIPE_SECRET'),
            'mode' => 'card_elements',
        ]);

        $this->paymentElementsGateway = new StripeGateway([
            'secret' => env('STRIPE_SECRET'),
            'mode' => 'payment_elements',
        ]);
    }

    /** @test */
    public function has_a_name()
    {
        $name = $this->cardElementsGateway->name();

        $this->assertIsString($name);
        $this->assertSame('Stripe', $name);
    }

    /** @test */
    public function can_prepare()
    {
        if (! env('STRIPE_SECRET')) {
            $this->markTestSkipped('Skipping, no Stripe Secret has been defined for this environment.');
        }

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

        $this->assertIsArray($prepare);

        $this->assertArrayHasKey('intent', $prepare);
        $this->assertArrayHasKey('client_secret', $prepare);

        $paymentIntent = PaymentIntent::retrieve($prepare['intent']);

        $this->assertSame($paymentIntent->id, $prepare['intent']);
        $this->assertSame($paymentIntent->amount, $order->grandTotal());
        $this->assertNull($paymentIntent->customer);
        $this->assertNull($paymentIntent->receipt_email);
    }

    /** @test */
    public function can_prepare_with_customer()
    {
        if (! env('STRIPE_SECRET')) {
            $this->markTestSkipped('Skipping, no Stripe Secret has been defined for this environment.');
        }

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

        $this->assertIsArray($prepare);

        $this->assertArrayHasKey('intent', $prepare);
        $this->assertArrayHasKey('client_secret', $prepare);

        $paymentIntent = PaymentIntent::retrieve($prepare['intent']);

        $this->assertSame($paymentIntent->id, $prepare['intent']);
        $this->assertSame($paymentIntent->amount, $order->grandTotal());
        $this->assertNotNull($paymentIntent->customer);
        $this->assertNull($paymentIntent->receipt_email);

        $stripeCustomer = StripeCustomer::retrieve($paymentIntent->customer);

        $this->assertSame($stripeCustomer->id, $paymentIntent->customer);
        $this->assertSame($stripeCustomer->name, 'George');
        $this->assertSame($stripeCustomer->email, 'george@example.com');
    }

    /** @test */
    public function can_prepare_with_receipt_email()
    {
        if (! env('STRIPE_SECRET')) {
            $this->markTestSkipped('Skipping, no Stripe Secret has been defined for this environment.');
        }

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

        $this->assertIsArray($prepare);

        $this->assertArrayHasKey('intent', $prepare);
        $this->assertArrayHasKey('client_secret', $prepare);

        $paymentIntent = PaymentIntent::retrieve($prepare['intent']);

        $this->assertSame($paymentIntent->id, $prepare['intent']);
        $this->assertSame($paymentIntent->amount, $order->grandTotal());
        $this->assertNotNull($paymentIntent->customer);
        $this->assertSame($paymentIntent->receipt_email, $customer->email());

        $stripeCustomer = StripeCustomer::retrieve($paymentIntent->customer);

        $this->assertSame($stripeCustomer->id, $paymentIntent->customer);
        $this->assertSame($stripeCustomer->name, 'George');
        $this->assertSame($stripeCustomer->email, 'george@example.com');
    }

    /** @test */
    public function can_prepare_with_payment_intent_data_closure()
    {
        if (! env('STRIPE_SECRET')) {
            $this->markTestSkipped('Skipping, no Stripe Secret has been defined for this environment.');
        }

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

        $this->assertIsArray($prepare);

        $this->assertArrayHasKey('intent', $prepare);
        $this->assertArrayHasKey('client_secret', $prepare);

        $paymentIntent = PaymentIntent::retrieve($prepare['intent']);

        $this->assertSame($paymentIntent->id, $prepare['intent']);
        $this->assertSame($paymentIntent->amount, $order->get('grand_total'));
        $this->assertSame($paymentIntent->description, 'Some custom description');
        $this->assertSame($paymentIntent->metadata->foo, 'bar');
        $this->assertNull($paymentIntent->customer);
        $this->assertNull($paymentIntent->receipt_email);

        $this->cardElementsGateway->setConfig([
            'secret' => env('STRIPE_SECRET'),
        ]);
    }

    /** @test */
    public function can_checkout_when_in_card_elements_mode()
    {
        if (! env('STRIPE_SECRET')) {
            $this->markTestSkipped('Skipping, no Stripe Secret has been defined for this environment.');
        }

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

        $this->assertIsArray($checkout);

        $this->assertSame($checkout['id'], $paymentMethod->id);
        $this->assertSame($checkout['object'], $paymentMethod->object);
        $this->assertSame($checkout['customer'], $paymentMethod->customer);
        $this->assertSame($checkout['livemode'], $paymentMethod->livemode);
        $this->assertSame($checkout['payment_intent'], $paymentIntent);

        $order = $order->fresh();

        $this->assertSame($order->paymentStatus(), PaymentStatus::Paid);
        $this->assertNotNull($order->statusLog('paid'));
    }

    /** @test */
    public function cant_checkout_when_in_payment_elements_mode()
    {
        if (! env('STRIPE_SECRET')) {
            $this->markTestSkipped('Skipping, no Stripe Secret has been defined for this environment.');
        }

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

        $this->assertSame($order->paymentStatus(), PaymentStatus::Unpaid);
        $this->assertNotNull($order->statusLog('paid'));
    }

    /** @test */
    public function has_checkout_rules()
    {
        $rules = (new StripeGateway())->checkoutRules();

        $this->assertIsArray($rules);

        $this->assertSame([
            'payment_method' => ['required', 'string'],
        ], $rules);
    }

    /** @test */
    public function can_refund_charge()
    {
        if (! env('STRIPE_SECRET')) {
            $this->markTestSkipped('Skipping, no Stripe Secret has been defined for this environment.');
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $order = Order::make()->grandTotal(1234)->gateway([
            'use' => StripeGateway::class,
            'data' => [
                'payment_intent' => $paymentIntent = PaymentIntent::create([
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

        $refund = $this->cardElementsGateway->refund($order);

        $this->assertIsArray($refund);

        $this->assertStringContainsString('re_', $refund['id']);
        $this->assertSame($refund['amount'], 1234);
        $this->assertSame($refund['payment_intent'], $paymentIntent);
    }

    /** @test */
    public function can_hit_webhook_with_payment_intent_succeeded_event()
    {
        $order = Order::make();
        $order->save();

        $payload = [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'metadata' => [
                        'order_id' => $order->id(),
                    ],
                ],
            ],
        ];

        $webhook = $this->paymentElementsGateway->webhook(new Request([], [], [], [], [], [], json_encode($payload)));

        $this->assertTrue($webhook instanceof Response);

        $order->fresh();

        $this->assertSame($order->status(), OrderStatus::Placed);
        $this->assertSame($order->paymentStatus(), PaymentStatus::Paid);
    }

    /** @test */
    public function returns_array_from_payment_display()
    {
        if (! env('STRIPE_SECRET')) {
            $this->markTestSkipped('Skipping, no Stripe Secret has been defined for this environment.');
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $fieldtypeDisplay = $this->cardElementsGateway->fieldtypeDisplay([
            'use' => StripeGateway::class,
            'data' => [
                'payment_intent' => $paymentIntent = PaymentIntent::create([
                    'amount' => 1234,
                    'currency' => 'GBP',
                ])->id,
            ],
        ]);

        $this->assertIsArray($fieldtypeDisplay);

        $this->assertSame([
            'text' => $paymentIntent,
            'url' => 'https://dashboard.stripe.com/test/payments/'.$paymentIntent,
        ], $fieldtypeDisplay);
    }

    /** @test */
    public function does_not_return_array_from_payment_display_if_no_payment_intent_is_set()
    {
        $fieldtypeDisplay = $this->cardElementsGateway->fieldtypeDisplay([
            'use' => StripeGateway::class,
            'data' => [],
        ]);

        $this->assertIsArray($fieldtypeDisplay);

        $this->assertSame([
            'text' => 'Unknown',
            'url' => null,
        ], $fieldtypeDisplay);
    }
}
