<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Gateways\Builtin;

use DoubleThreeDigital\SimpleCommerce\Gateways\Prepare;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Gateways\Builtin\StripeGateway;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Http\Request;
use Statamic\Facades\Collection;

class StripeGatewayTest extends TestCase
{
    public $gateway;

    public function setUp(): void
    {
        parent::setUp();

        if (! env('STRIPE_SECRET')) {
            $this->markTestSkipped("No STRIPE_SECRET has been defined, tests has been skipped.");
        }

        $this->gateway = new StripeGateway([
            'secret' => env('STRIPE_SECRET'),
        ]);

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

        $prepare = $this->gateway->prepare(new Prepare(
            new Request(),
            Order::create()->entry()
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
            Order::create()->entry()
        );

        $this->assertIsObject($charge);
        $this->assertSame([], $charge);
    }

    /** @test */
    public function can_refund_charge()
    {
        $this->markTestIncomplete();

        $refund = (new StripeGateway())->refundCharge(
            Order::create()
        );

        $this->assertIsObject($refund);
        $this->assertTrue($refund->success);
    }

    /** @test */
    public function can_hit_webhook()
    {
        $this->markTestIncomplete();

        $webhook = $this->gateway->webhook(new Request());

        $this->assertSame($webhook, null);
    }
}
