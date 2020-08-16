<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Gateways;

use DoubleThreeDigital\SimpleCommerce\Gateways\StripeGateway;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;

class StripeGatewayTest extends TestCase
{
    public $gateway;

    public function setUp(): void
    {
        parent::setUp();

        $this->gateway = new StripeGateway();
    }

    /** @test */
    public function has_a_name()
    {
        $name = $this->gateway->name();

        $this->assertSame('Stripe', $name);
    }

    /** @test */
    public function can_prepare()
    {
        // if (! isset($_SERVER['STRIPE_KEY']) && ! isset($_SERVER['STRIPE_SECRET'])) {
            $this->markTestSkipped();
        // }

        $prepare = $this->gateway->prepare([
            'grand_total' => 1200,
        ]);

        $this->assertArrayHasKey('intent', $prepare);
        $this->assertArrayHasKey('client_secret', $prepare);
    }

    /** @test */
    public function can_purchase()
    {
        // if (! isset($_SERVER['STRIPE_KEY']) && ! isset($_SERVER['STRIPE_SECRET'])) {
            $this->markTestSkipped();
        // }

        // TODO: need to figure out how to make a payment intent for testing, as its created on the client side
    }

    /** @test */
    public function has_purchase_rules()
    {
        $rules = (new StripeGateway())->purchaseRules();

        $this->assertIsArray($rules);
        $this->assertSame([
            'payment_method' => 'required|string',
        ], $rules);
    }

    /** @test */
    public function can_get_charge()
    {
        $charge = (new StripeGateway())->getCharge([]);

        $this->assertIsArray($charge);
        $this->assertSame([], $charge);
    }

    /** @test */
    public function can_refund_charge()
    {
        $this->markTestIncomplete();

        $refund = (new StripeGateway())->refundCharge([]);

        $this->assertIsArray($refund);
        $this->assertSame([], $refund);
    }
}
