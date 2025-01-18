<?php

namespace Tests\Feature\Payments;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Payments\Gateways\Stripe;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Tests\TestCase;

#[Group('payments')]
class StripeTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        \Stripe\Stripe::setApiKey(config('statamic.simple-commerce.payments.gateways.stripe.secret'));
    }

    #[Test]
    public function it_can_setup_a_payment()
    {
        $cart = $this->makeCartWithGuestCustomer();

        $setup = (new Stripe)->setup($cart);

        $this->assertArrayHasKey('client_secret', $setup);

        $cart->fresh();

        $this->assertNotNull($cart->get('stripe_payment_intent'));

        $stripePaymentIntent = PaymentIntent::retrieve($cart->get('stripe_payment_intent'));

        $this->assertEquals(1000, $stripePaymentIntent->amount);
        $this->assertEquals('requires_payment_method', $stripePaymentIntent->status);
        $this->assertEquals(['cart_id' => $cart->id()], $stripePaymentIntent->metadata->toArray());
    }

    #[Test]
    public function it_can_setup_a_payment_for_a_new_customer()
    {
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
        $stripePaymentIntent = PaymentIntent::create(['amount' => 1000, 'currency' => 'gbp']);

        $cart = $this->makeCartWithGuestCustomer();
        $cart->set('stripe_payment_intent', $stripePaymentIntent->id)->save();

        $setup = (new Stripe)->setup($cart);

        $this->assertEquals($stripePaymentIntent->client_secret, $setup['client_secret']);

        $cart->fresh();

        $this->assertEquals($stripePaymentIntent->id, $cart->get('stripe_payment_intent'));
    }

    #[Test]
    public function payment_intent_amount_is_updated_after_totals_are_recalculated()
    {
        $stripePaymentIntent = PaymentIntent::create(['amount' => 1000, 'currency' => 'gbp']);

        $cart = $this->makeCartWithGuestCustomer();
        $cart->set('stripe_payment_intent', $stripePaymentIntent->id)->save();

        $cart->grandTotal(2000)->saveWithoutRecalculating();

        (new Stripe)->afterRecalculating($cart);

        $cart->fresh();
        $this->assertEquals($stripePaymentIntent->id, $cart->get('stripe_payment_intent'));

        $stripePaymentIntent = PaymentIntent::retrieve($cart->get('stripe_payment_intent'));
        $this->assertEquals(2000, $stripePaymentIntent->amount);
    }

    #[Test]
    public function it_can_process_a_payment()
    {
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
    public function it_receives_a_payment_intent_refunded_webhook_event()
    {
        $this->markTestIncomplete('TODO Refunds');
    }

    #[Test]
    public function it_refunds_a_payment()
    {
        $this->markTestIncomplete('TODO Refunds');
    }

    private function makeCartWithGuestCustomer()
    {
        Collection::make('products')->save();
        Entry::make()->id('product-id')->collection('products')->data(['price' => 1000])->save();

        $cart = Cart::make()
            ->lineItems([
                ['product' => 'product-id', 'quantity' => 1, 'total' => 1000],
            ])
            ->customer(['name' => 'David Hasselhoff', 'email' => 'david@hasselhoff.com']);

        $cart->save();

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
