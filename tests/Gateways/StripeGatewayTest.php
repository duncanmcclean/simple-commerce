<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Gateways;

use DoubleThreeDigital\SimpleCommerce\Gateways\StripeGateway;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class StripeGatewayTest extends TestCase
{
    public $gateway;

    public function setUp(): void
    {
        parent::setUp();

        $this->gateway = new StripeGateway();
    }

    /** @test */
    public function can_complete_purchase()
    {
        //
    }

    /** @test */
    public function can_return_validation_rules()
    {
        //
    }

    /** @test */
    public function can_return_payment_form()
    {
        //
    }

    /** @test */
    public function can_issue_refund()
    {
        //
    }

    /** @test */
    public function can_return_name()
    {
        $name = $this->gateway->name();

        $this->assertSame($name, 'Stripe');
    }
}
