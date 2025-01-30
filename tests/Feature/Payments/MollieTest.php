<?php

namespace Feature\Payments;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Payments\Gateways\Mollie;
use DuncanMcClean\SimpleCommerce\Payments\Gateways\Stripe;
use Illuminate\Support\Facades\Config;
use Mollie\Api\MollieApiClient;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Tests\TestCase;

#[Group('payments')]
class MollieTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    private $mollie;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('statamic.simple-commerce.payments.gateways', ['mollie' => [
            'api_key' => env('MOLLIE_KEY'),
            'profile_id' => env('MOLLIE_PROFILE_ID'),
        ]]);

        $this->mollie = new MollieApiClient;
        $this->mollie->setApiKey(env('MOLLIE_KEY'));
    }

    #[Test]
    public function it_can_setup_a_payment()
    {
        $cart = $this->makeCartWithGuestCustomer();

        $setup = (new Mollie)->setup($cart);

        $this->assertArrayHasKey('checkout_url', $setup);

        $cart->fresh();

        $this->assertNotNull($cart->get('mollie_payment_id'));

        $payment = $this->mollie->payments->get($cart->get('mollie_payment_id'));

        $this->assertEquals("Pending Order: {$cart->id()}", $payment->description);
        $this->assertEquals('10.00', $payment->amount->value);
        $this->assertEquals($cart->id(), $payment->metadata->cart_id);

        // todo: get the vatAmount validation passing

        $this->assertEquals([
            [
                'description' => 'Foobar',
                'type' => 'physical',
                'quantity' => 1,
                'unitPrice' => ['value' => '8.33', 'currency' => 'GBP'],
                'vatRate' => 20,
                'vatAmount' => ['value' => '1.67', 'currency' => 'GBP'],
                'totalAmount' => ['value' => '10.00', 'currency' => 'GBP'],
                'productUrl' => null,
            ],
        ], json_decode(json_encode($payment->lines), true));

        // todo: test that the address is included
        // todo: test that taxes work
        // todo: test that discounts work
        // todo: ensure that shipping adds a new line item
    }

    #[Test]
    public function it_can_setup_a_payment_for_a_new_customer()
    {
        $this->markTestIncomplete();

        [$cart, $user] = $this->makeCartAndUser();

        (new Stripe)->setup($cart);

        $user = $user->fresh();
        $this->assertNotNull($user->get('stripe_customer_id'));

        $stripeCustomer = Customer::retrieve($user->get('stripe_customer_id'));

        $this->assertEquals('David Hasselhoff', $stripeCustomer->name);
        $this->assertEquals('david@hasselhoff.com', $stripeCustomer->email);
    }

    #[Test]
    public function it_can_setup_a_payment_for_an_existing_customer()
    {
        $this->markTestIncomplete();

        $stripeCustomer = Customer::create([
            'name' => 'David Hasselhoff',
            'email' => 'david@hasselhoff.com',
        ]);

        [$cart, $user] = $this->makeCartAndUser();
        $user->set('stripe_customer_id', $stripeCustomer->id)->save();

        (new Stripe)->setup($cart);

        $user = $user->fresh();
        $this->assertEquals($stripeCustomer->id, $user->get('stripe_customer_id'));
    }

    #[Test]
    public function setup_returns_existing_payment_intent_if_one_exists()
    {
        $this->markTestIncomplete();

        $stripePaymentIntent = PaymentIntent::create(['amount' => 1000, 'currency' => 'gbp']);

        $cart = $this->makeCartWithGuestCustomer();
        $cart->set('stripe_payment_intent', $stripePaymentIntent->id)->save();

        $setup = (new Stripe)->setup($cart);

        $this->assertEquals($stripePaymentIntent->client_secret, $setup['client_secret']);

        $cart->fresh();

        $this->assertEquals($stripePaymentIntent->id, $cart->get('stripe_payment_intent'));
    }

    #[Test]
    public function it_can_process_a_payment()
    {
        $this->markTestIncomplete();

        $stripePaymentIntent = PaymentIntent::create(['amount' => 1000, 'currency' => 'gbp']);

        $order = $this->makeOrder();

        $order
            ->orderNumber(1234)
            ->set('stripe_payment_intent', $stripePaymentIntent->id)
            ->save();

        (new Stripe)->process($order);

        $order->fresh();

        $this->assertEquals('stripe', $order->get('payment_gateway'));

        $stripePaymentIntent = PaymentIntent::retrieve($order->get('stripe_payment_intent'));
        $this->assertEquals('Order #1234', $stripePaymentIntent->description);
        $this->assertEquals([
            'order_id' => $order->id(),
            'order_number' => $order->orderNumber(),
        ], $stripePaymentIntent->metadata->toArray());
    }

    #[Test]
    public function it_can_capture_a_payment()
    {
        $this->markTestIncomplete();

        $stripePaymentIntent = PaymentIntent::create([
            'amount' => 1000,
            'currency' => 'gbp',
            'payment_method_types' => ['card'],
            'capture_method' => 'manual',
        ]);

        $stripePaymentIntent->confirm(['payment_method' => 'pm_card_visa']);

        $order = $this->makeOrder();
        $order->set('stripe_payment_intent', $stripePaymentIntent->id)->save();

        (new Stripe)->capture($order);

        $stripePaymentIntent = PaymentIntent::retrieve($order->get('stripe_payment_intent'));
        $this->assertEquals('succeeded', $stripePaymentIntent->status);

        $order->fresh();
        $this->assertEquals('payment_received', $order->status()->value);
    }

    #[Test]
    public function it_can_cancel_a_payment()
    {
        $this->markTestIncomplete();

        $stripePaymentIntent = PaymentIntent::create(['amount' => 1000, 'currency' => 'gbp']);

        $cart = $this->makeCartWithGuestCustomer();
        $cart->set('stripe_payment_intent', $stripePaymentIntent->id)->save();

        (new Stripe)->cancel($cart);

        $cart->fresh();
        $this->assertFalse($cart->has('stripe_payment_intent'));

        $stripePaymentIntent = PaymentIntent::retrieve($stripePaymentIntent->id);
        $this->assertEquals('canceled', $stripePaymentIntent->status);
    }

    #[Test]
    public function it_verifies_the_webhook_signature()
    {
        $this->markTestIncomplete();

        Config::set('statamic.simple-commerce.payments.gateways.stripe.webhook_secret', 'whsec_test_secret');

        $this
            ->post(uri: '/!/simple-commerce/payments/stripe/webhook', headers: [
                'Stripe-Signature' => 'invalid-signature',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function it_receives_a_payment_intent_amount_capturable_updated_webhook_event()
    {
        $this->markTestIncomplete();

        $stripePaymentIntent = PaymentIntent::create([
            'amount' => 1000,
            'currency' => 'gbp',
            'payment_method_types' => ['card'],
            'capture_method' => 'manual',
        ]);

        $stripePaymentIntent->confirm(['payment_method' => 'pm_card_visa']);

        $order = $this->makeOrder();
        $order->set('stripe_payment_intent', $stripePaymentIntent->id)->save();

        $this
            ->post(
                uri: '/!/simple-commerce/payments/stripe/webhook',
                data: [
                    'type' => 'payment_intent.amount_capturable_updated',
                    'data' => [
                        'object' => [
                            'id' => $stripePaymentIntent->id,
                        ],
                    ],
                ]
            )
            ->assertOk();

        $stripePaymentIntent = PaymentIntent::retrieve($order->get('stripe_payment_intent'));
        $this->assertEquals('succeeded', $stripePaymentIntent->status);

        $order->fresh();
        $this->assertEquals('payment_received', $order->status()->value);
    }

    #[Test]
    public function it_receives_a_payment_intent_succeeded_webhook_event()
    {
        $this->markTestIncomplete();

        $stripePaymentIntent = PaymentIntent::create([
            'amount' => 1000,
            'currency' => 'gbp',
            'payment_method_types' => ['card'],
        ]);

        $stripePaymentIntent->confirm(['payment_method' => 'pm_card_visa']);

        $order = $this->makeOrder();
        $order->set('stripe_payment_intent', $stripePaymentIntent->id)->save();

        $this
            ->post(
                uri: '/!/simple-commerce/payments/stripe/webhook',
                data: [
                    'type' => 'payment_intent.succeeded',
                    'data' => [
                        'object' => [
                            'id' => $stripePaymentIntent->id,
                        ],
                    ],
                ],
            )
            ->assertOk();

        $order->fresh();
        $this->assertEquals('payment_received', $order->status()->value);
    }

    #[Test]
    public function it_receives_a_charge_refunded_webhook_event()
    {
        $this->markTestIncomplete();

        $stripePaymentIntent = PaymentIntent::create([
            'amount' => 1000,
            'currency' => 'gbp',
            'payment_method_types' => ['card'],
        ]);

        $stripePaymentIntent->confirm(['payment_method' => 'pm_card_visa']);

        $order = $this->makeOrder();
        $order->set('stripe_payment_intent', $stripePaymentIntent->id)->save();

        $this
            ->post(
                uri: '/!/simple-commerce/payments/stripe/webhook',
                data: [
                    'type' => 'charge.refunded',
                    'data' => [
                        'object' => [
                            'id' => $stripePaymentIntent->latest_charge,
                            'payment_intent' => $stripePaymentIntent->id,
                            'amount_refunded' => 750,
                        ],
                    ],
                ],
            )
            ->assertOk();

        $order->fresh();
        $this->assertEquals(750, $order->get('amount_refunded'));
    }

    #[Test]
    public function it_refunds_a_payment()
    {
        $this->markTestIncomplete();

        $stripePaymentIntent = PaymentIntent::create([
            'amount' => 1000,
            'currency' => 'gbp',
            'payment_method_types' => ['card'],
        ]);

        $stripePaymentIntent->confirm(['payment_method' => 'pm_card_visa']);

        $order = $this->makeOrder();
        $order->set('stripe_payment_intent', $stripePaymentIntent->id)->save();

        (new Stripe)->refund($order, 750);

        $charge = Charge::retrieve($stripePaymentIntent->latest_charge);
        $this->assertEquals(750, $charge->amount_refunded);
    }

    private function makeCartWithGuestCustomer()
    {
        Collection::make('products')->save();
        Entry::make()->id('product-id')->collection('products')->data(['title' => 'Foobar', 'price' => 1000])->save();

        $cart = Cart::make()
            ->lineItems([
                [
                    'product' => 'product-id',
                    'quantity' => 1,
                    'tax_total' => 167,
                    'tax_breakdown' => [
                        ['rate' => 20, 'total' => 167],
                    ],
                    'total' => 1000,
                ],
            ])
            ->customer(['name' => 'David Hasselhoff', 'email' => 'david@hasselhoff.com']);

        $cart->saveWithoutRecalculating();

        return $cart;
    }

    private function makeCartAndUser(): array
    {
        Collection::make('products')->save();
        Entry::make()->id('product-id')->collection('products')->data(['price' => 1000])->save();

        $user = User::make()
            ->email('david@hasselhoff.com')
            ->set('name', 'David Hasselhoff');

        $user->save();

        $cart = Cart::make()
            ->lineItems([
                ['product' => 'product-id', 'quantity' => 1, 'total' => 1000],
            ])
            ->customer($user);

        $cart->save();

        return [$cart, $user];
    }

    private function makeOrder(): OrderContract
    {
        $order = Order::make()
            ->status(OrderStatus::PaymentPending)
            ->grandTotal(1000)
            ->lineItems([
                ['product' => 'product-id', 'quantity' => 1, 'total' => 1000],
            ]);

        $order->save();

        return $order;
    }
}
