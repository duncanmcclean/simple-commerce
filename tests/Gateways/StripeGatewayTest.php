<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Gateways;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Gateways\GatewayPrep;
use DoubleThreeDigital\SimpleCommerce\Gateways\StripeGateway;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Http\Request;
use Statamic\Facades\Collection;
use Stripe\PaymentIntent;

class StripeGatewayTest extends TestCase
{
    public $gateway;

    public function setUp(): void
    {
        parent::setUp();

        $this->gateway = new StripeGateway();

        Collection::make('orders')->title('Order')->save();
    }

    /** @test */
    public function has_a_name()
    {
        $name = $this->gateway->name();

        $this->assertIsString($name);
        $this->assertSame('Stripe', $name);
    }

    /** @test */
    public function can_prepare()
    {
        $this->markTestSkipped();

        $prepare = $this->gateway->prepare(new GatewayPrep(
            new Request(),
            Cart::make()->save()->entry()
        ));

        $this->assertIsObject($prepare);
        $this->assertTrue($prepare->success());
        $this->assertArrayHasKey('intent', $prepare->data());
        $this->assertArrayHasKey('client_secret', $prepare->data());
    }

    /** @test */
    public function can_purchase()
    {
        // TODO: Write test for this that doesn't need to touch the Stripe API
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
        // TODO: Write test for this that doesn't need to touch the Stripe API
        $this->markTestSkipped();

        $charge = (new StripeGateway())->getCharge(
            Cart::make()->save()->entry()
        );

        $this->assertIsObject($charge);
        $this->assertSame([], $charge);
    }

    /** @test */
    public function can_refund_charge()
    {
        $this->markTestIncomplete();

        $refund = (new StripeGateway())->refundCharge(
            Cart::make()->save()->entry()
        );

        $this->assertIsObject($refund);
        $this->assertTrue($refund->success);
    }

    /** @test */
    public function can_hit_webhook()
    {
        $webhook = $this->gateway->webhook(new Request());

        $this->assertSame($webhook, null);
    }
}
