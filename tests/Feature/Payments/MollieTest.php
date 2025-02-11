<?php

namespace Tests\Feature\Payments;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Payments\Gateways\Mollie;
use Exception;
use Mockery;
use Mollie\Api\MollieApiClient;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('payments')]
class MollieTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    private MollieApiClient $mollie;

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

    protected function tearDown(): void
    {
        parent::tearDown();

        // Mollie enforces strict rate limits, so we need to sleep between tests to avoid hitting them.
        sleep(2);
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

        $this->assertEquals(config('app.name'), $payment->description);
        $this->assertEquals('10.00', $payment->amount->value);
        $this->assertEquals($cart->id(), $payment->metadata->cart_id);

        $this->assertEquals([
            [
                'description' => 'Foobar',
                'type' => 'physical',
                'quantity' => 1,
                'unitPrice' => ['value' => '10.00', 'currency' => 'GBP'],
                'vatRate' => '20.00',
                'vatAmount' => ['value' => '1.67', 'currency' => 'GBP'],
                'totalAmount' => ['value' => '10.00', 'currency' => 'GBP'],
            ],
        ], json_decode(json_encode($payment->lines), true));
    }

    #[Test]
    public function it_can_setup_a_payment_when_prices_exclude_tax()
    {
        config()->set('statamic.simple-commerce.taxes.price_includes_tax', false);

        $cart = $this->makeCartWithGuestCustomer()
            ->grandTotal(1200)
            ->lineItems([
                [
                    'product' => 'product-id',
                    'quantity' => 1,
                    'unit_price' => 1000,
                    'sub_total' => 1000,
                    'tax_total' => 200,
                    'tax_breakdown' => [['rate' => 20, 'amount' => 200]],
                    'total' => 1200,
                ],
            ]);

        $cart->saveWithoutRecalculating();

        (new Mollie)->setup($cart);

        $payment = $this->mollie->payments->get($cart->fresh()->get('mollie_payment_id'));

        $this->assertEquals([
            [
                'description' => 'Foobar',
                'type' => 'physical',
                'quantity' => 1,
                'unitPrice' => ['value' => '12.00', 'currency' => 'GBP'],
                'vatRate' => '20.00',
                'vatAmount' => ['value' => '2.00', 'currency' => 'GBP'],
                'totalAmount' => ['value' => '12.00', 'currency' => 'GBP'],
            ],
        ], json_decode(json_encode($payment->lines), true));
    }

    #[Test]
    public function it_can_setup_a_payment_with_discounted_line()
    {
        $cart = $this->makeCartWithGuestCustomer()
            ->grandTotal(900)
            ->lineItems([
                [
                    'product' => 'product-id',
                    'quantity' => 1,
                    'discount_amount' => 100,
                    'unit_price' => 1000,
                    'sub_total' => 1000,
                    'tax_total' => 150,
                    'tax_breakdown' => [['rate' => 20, 'amount' => 150]],
                    'total' => 900,
                ],
            ]);

        $cart->saveWithoutRecalculating();

        (new Mollie)->setup($cart);

        $payment = $this->mollie->payments->get($cart->fresh()->get('mollie_payment_id'));

        $this->assertEquals([
            [
                'description' => 'Foobar',
                'type' => 'physical',
                'quantity' => 1,
                'unitPrice' => ['value' => '10.00', 'currency' => 'GBP'],
                'vatRate' => '20.00',
                'vatAmount' => ['value' => '1.50', 'currency' => 'GBP'],
                'discountAmount' => ['value' => '1.00', 'currency' => 'GBP'],
                'totalAmount' => ['value' => '9.00', 'currency' => 'GBP'],
            ],
        ], json_decode(json_encode($payment->lines), true));
    }

    #[Test]
    public function selected_shipping_option_is_added_as_a_payment_line()
    {
        $cart = $this->makeCartWithGuestCustomer();
        $cart->merge(['shipping_method' => 'free_shipping', 'shipping_option' => 'free_shipping'])->save();

        (new Mollie)->setup($cart);

        $payment = $this->mollie->payments->get($cart->fresh()->get('mollie_payment_id'));

        $this->assertEquals([
            'description' => 'Free Shipping',
            'type' => 'shipping_fee',
            'quantity' => 1,
            'unitPrice' => ['value' => '0.00', 'currency' => 'GBP'],
            'vatRate' => '0.00',
            'vatAmount' => ['value' => '0.00', 'currency' => 'GBP'],
            'totalAmount' => ['value' => '0.00', 'currency' => 'GBP'],
        ], json_decode(json_encode($payment->lines), true)[1]);
    }

    #[Test]
    public function addresses_are_added_to_payments()
    {
        $cart = $this->makeCartWithGuestCustomer();

        $cart->data([
            'shipping_line_1' => '123 Fake St',
            'shipping_city' => 'Fakeville',
            'shipping_postcode' => 'FA 1234',
            'shipping_country' => 'USA',
            'shipping_state' => 'CA',
            'billing_line_1' => '123 Fake Road',
            'billing_city' => 'Faketown',
            'billing_postcode' => 'FA 5678',
            'billing_country' => 'USA',
            'billing_state' => 'CA',
        ])->save();

        (new Mollie)->setup($cart);

        $payment = $this->mollie->payments->get($cart->fresh()->get('mollie_payment_id'));

        $this->assertEquals([
            'streetAndNumber' => '123 Fake Road',
            'postalCode' => 'FA 5678',
            'city' => 'Faketown',
            'country' => 'US',
        ], (array) $payment->billingAddress);

        $this->assertEquals([
            'streetAndNumber' => '123 Fake St',
            'postalCode' => 'FA 1234',
            'city' => 'Fakeville',
            'country' => 'US',
        ], (array) $payment->shippingAddress);
    }

    #[Test]
    public function it_can_setup_a_payment_for_a_new_customer()
    {
        [$cart, $user] = $this->makeCartAndUser();

        (new Mollie)->setup($cart);

        $user = $user->fresh();
        $this->assertNotNull($user->get('mollie_customer_id'));

        $mollieCustomer = $this->mollie->customers->get($user->get('mollie_customer_id'));

        $this->assertEquals('David Hasselhoff', $mollieCustomer->name);
        $this->assertEquals('david@hasselhoff.com', $mollieCustomer->email);
    }

    #[Test]
    public function it_can_setup_a_payment_for_an_existing_customer()
    {
        $mollieCustomer = $this->mollie->customers->create([
            'name' => 'David Hasselhoff',
            'email' => 'david@hasselhoff.com',
        ]);

        [$cart, $user] = $this->makeCartAndUser();
        $user->set('mollie_customer_id', $mollieCustomer->id)->save();

        (new Mollie)->setup($cart);

        $user = $user->fresh();
        $this->assertEquals($mollieCustomer->id, $user->get('mollie_customer_id'));
    }

    #[Test]
    public function setup_returns_existing_payment_if_one_exists()
    {
        $molliePayment = $this->mollie->payments->create([
            'description' => 'Test payment',
            'amount' => ['currency' => 'GBP', 'value' => '10.00'],
            'redirectUrl' => 'https://example.com/redirect',
            'metadata' => ['cart_fingerprint' => 'original'],
        ]);

        $cart = $this->makeCartWithGuestCustomer();
        $cart->set('mollie_payment_id', $molliePayment->id)->save();

        $mock = Mockery::mock($cart);
        $mock->shouldReceive('fingerprint')->andReturn('original');

        $setup = (new Mollie)->setup($mock);

        $this->assertEquals($molliePayment->getCheckoutUrl(), $setup['checkout_url']);
        $this->assertEquals($molliePayment->id, $cart->fresh()->get('mollie_payment_id'));
    }

    #[Test]
    public function setup_returns_new_payment_when_cart_fingerprint_has_changed()
    {
        $originalPayment = $this->mollie->payments->create([
            'description' => 'Test payment',
            'amount' => ['currency' => 'GBP', 'value' => '10.00'],
            'redirectUrl' => 'https://example.com/redirect',
            'metadata' => ['cart_fingerprint' => 'original'],
        ]);

        $cart = $this->makeCartWithGuestCustomer();
        $cart->set('mollie_payment_id', $originalPayment->id)->save();

        $mock = Mockery::mock($cart);
        $mock->shouldReceive('fingerprint')->andReturn('changed');

        (new Mollie)->setup($mock);

        $this->assertNotEquals($originalPayment->id, $cart->fresh()->get('mollie_payment_id'));

        $originalPayment = $this->mollie->payments->get($originalPayment->id);
        $this->assertEquals('Outdated payment', $originalPayment->description);
    }

    #[Test]
    public function it_can_process_a_payment()
    {
        $molliePayment = $this->mollie->payments->create([
            'description' => 'Test payment',
            'amount' => ['currency' => 'GBP', 'value' => '10.00'],
            'redirectUrl' => 'https://example.com/redirect',
            'metadata' => ['cart_id' => 'foo', 'cart_fingerprint' => 'original'],
        ]);

        $order = $this->makeOrder();

        $order
            ->orderNumber(1234)
            ->set('mollie_payment_id', $molliePayment->id)
            ->save();

        (new Mollie)->process($order);

        $order->fresh();

        $molliePayment = $this->mollie->payments->get($order->get('mollie_payment_id'));
        $this->assertEquals('Order #1234', $molliePayment->description);
        $this->assertEquals([
            'cart_id' => 'foo',
            'cart_fingerprint' => 'original',
            'order_id' => $order->id(),
            'order_number' => $order->orderNumber(),
        ], (array) $molliePayment->metadata);
    }

    #[Test]
    public function it_cant_capture_a_payment()
    {
        $order = $this->makeOrder();

        $this->expectException(Exception::class);

        (new Mollie)->capture($order);
    }

    #[Test]
    public function it_can_cancel_a_payment()
    {
        // TODO: Figure out how to create a "paid" payment in Mollie in order to test this.
        $this->markTestIncomplete();
    }

    #[Test]
    public function it_receives_a_webhook_with_cancelled_payment()
    {
        // TODO: Figure out how to create a "cancelled" payment in Mollie in order to test this.
        $this->markTestIncomplete();

        $molliePayment = $this->mollie->payments->create([
            'description' => 'Test payment',
            'amount' => ['currency' => 'GBP', 'value' => '10.00'],
            'redirectUrl' => 'https://example.com/redirect',
            'metadata' => ['cart_fingerprint' => 'original'],
        ]);

        $order = $this->makeOrder();
        $order->set('mollie_payment_id', $molliePayment->id)->save();

        $this
            ->post('/!/simple-commerce/payments/mollie/webhook', ['id' => $molliePayment->id])
            ->assertOk();

        $this->assertNull(Order::find($order->id()));
    }

    #[Test]
    public function it_receives_a_webhook_with_paid_payment()
    {
        // TODO: Figure out how to create a "paid" payment in Mollie in order to test this.
        $this->markTestIncomplete();

        $molliePayment = $this->mollie->payments->create([
            'description' => 'Test payment',
            'amount' => ['currency' => 'GBP', 'value' => '10.00'],
            'redirectUrl' => 'https://example.com/redirect',
            'metadata' => ['cart_fingerprint' => 'original'],
        ]);

        $order = $this->makeOrder();
        $order->set('mollie_payment_id', $molliePayment->id)->save();

        $this
            ->post('/!/simple-commerce/payments/mollie/webhook', ['id' => $molliePayment->id])
            ->assertOk();

        $this->assertEquals(OrderStatus::PaymentReceived, $order->fresh()->status());
    }

    #[Test]
    public function it_receives_a_webhook_with_refunded_payment()
    {
        // TODO: Figure out how to create a "refunded" payment in Mollie in order to test this.
        $this->markTestIncomplete();

        $molliePayment = $this->mollie->payments->create([
            'description' => 'Test payment',
            'amount' => ['currency' => 'GBP', 'value' => '10.00'],
            'redirectUrl' => 'https://example.com/redirect',
            'metadata' => ['cart_fingerprint' => 'original'],
        ]);

        $order = $this->makeOrder();
        $order->set('mollie_payment_id', $molliePayment->id)->save();

        $this
            ->post('/!/simple-commerce/payments/mollie/webhook', ['id' => $molliePayment->id])
            ->assertOk();

        $this->assertEquals(1000, $order->fresh()->get('amount_refunded'));
    }

    #[Test]
    public function it_refunds_a_payment()
    {
        // TODO: Figure out how to create a "paid" payment in Mollie in order to test this.
        $this->markTestIncomplete();
    }

    private function makeCartWithGuestCustomer()
    {
        Collection::make('products')->save();
        Entry::make()->id('product-id')->collection('products')->data(['title' => 'Foobar', 'price' => 1000])->save();

        $cart = Cart::make()
            ->grandTotal(1000)
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
        Entry::make()->id('product-id')->collection('products')->data(['title' => 'Foobar', 'price' => 1000])->save();

        $user = User::make()
            ->email('david@hasselhoff.com')
            ->set('name', 'David Hasselhoff');

        $user->save();

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
