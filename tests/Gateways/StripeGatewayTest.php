<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Gateways;

use DoubleThreeDigital\SimpleCommerce\Gateways\StripeGateway;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class StripeGatewayTest extends TestCase
{
    /** @test */
    public function has_a_name()
    {
        $name = (new StripeGateway)->name();

        $this->assertSame('Stripe', $name);
    }

    /** @test */
    public function can_prepare()
    {
        // TODO: stripe calls its api now
    }

    /** @test */
    public function can_purchase()
    {
        // TODO: stripe calls its api now
    }

    /** @test */
    public function has_purchase_rules()
    {
        $rules = (new StripeGateway)->purchaseRules();

        $this->assertIsArray($rules);
        $this->assertSame([
            'payment_method' => 'required|string',
        ], $rules);
    }

    /** @test */
    public function can_get_charge()
    {
        $charge = (new StripeGateway)->getCharge([]);

        $this->assertIsArray($charge);
        $this->assertSame([], $charge);
    }

    /** @test */
    public function can_refund_charge()
    {
        $refund = (new StripeGateway)->refundCharge([]);

        $this->assertIsArray($refund);
        $this->assertSame([], $refund);
    }
}