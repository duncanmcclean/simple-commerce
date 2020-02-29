<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Gateways;

use DoubleThreeDigital\SimpleCommerce\StripeGateway;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Facades\Stripe\Refund;

class StripeGatewayTest extends TestCase
{
    public $gateway;

    public function setUp(): void
    {
        parent::setUp();

        $this->gateway = new StripeGateway();
    }

    /** @test */
    public function can_issue_refund()
    {
        // TODO: figure out the best way to test this, as it touches Stripe
    }

    /** @test */
    public function can_setup_intent()
    {
        // TODO: figure out the best way to test this, as it touches Stripe
    }

    /** @test */
    public function can_complete_intent()
    {
        // TODO: figure out the best way to test this, as it touches Stripe
    }
}
