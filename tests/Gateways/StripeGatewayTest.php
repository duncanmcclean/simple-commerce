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

    /** @test */
    public function can_generate_random_payment_method()
    {
        $method = $this->gateway->randomPaymentMethod();

        $this->assertIsString($method);
    }

    /** @test */
    public function can_return_valid_card_number()
    {
        $card = $this->gateway->valid();

        $this->assertIsString($card);

        $this->assertTrue(in_array($card, [
            '4242424242424242',
            '4000056655665556',
            '5555555555554444',
            '2223003122003222',
        ]));
    }

    /** @test */
    public function can_return_american_card_number()
    {
        $card = $this->gateway->american();

        $this->assertIsString($card);

        $this->assertTrue(in_array($card, [
            '4000000760000002',
            '4000001240000000',
            '4012888888881881',
            '4000004840008001',
        ]));
    }

    /** @test */
    public function can_return_european_card_number()
    {
        $card = $this->gateway->european();

        $this->assertIsString($card);

        $this->assertTrue(in_array($card, [
            '4000000400000008',
            '4000000560000004',
            '4000002080000001',
            '4000007240000007',
            '4000007520000008',
            '4000007560000009',
            '4000008260000000',
            '4000058260000005',
        ]));
    }

    /** @test */
    public function can_return_asia_pacific_card_number()
    {
        $card = $this->gateway->asiaPacific();

        $this->assertIsString($card);

        $this->assertTrue(in_array($card, [
            '4000000360000006',
            '4000003440000004',
            '3530111333300000',
            '4000005540000008',
            '4000001560000002',
        ]));
    }

    /** @test */
    public function can_return_one_time_payment_auth_card_number()
    {
        $card = $this->gateway->oneTimePaymentAuth();

        $this->assertIsString($card);
        $this->assertSame($card, '4000002500003155');
    }

    /** @test */
    public function can_return_one_time_payment_auth_failure_card_number()
    {
        $card = $this->gateway->oneTimePaymentAuthFailure();

        $this->assertIsString($card);
        $this->assertSame($card, '4000008260003178');
    }

    /** @test */
    public function can_return_every_time_payment_auth_card_number()
    {
        $card = $this->gateway->everyTimePaymentAuth();

        $this->assertIsString($card);
        $this->assertSame($card, '4000002760003184');
    }

    /** @test */
    public function can_return_require_3d_secure_with_declined_card_number()
    {
        $card = $this->gateway->require3DSecureWithDeclinedCard();

        $this->assertIsString($card);
        $this->assertSame($card, '4000008400001629');
    }

    /** @test */
    public function can_return_incorrect_cvc_card_number()
    {
        $card = $this->gateway->incorrectCvc();

        $this->assertIsString($card);
        $this->assertSame($card, '4000000000000127');
    }

    /** @test */
    public function can_return_card_declined_card_number()
    {
        $card = $this->gateway->cardDeclined();

        $this->assertIsString($card);
        $this->assertSame($card, '4000000000000002');
    }

    /** @test */
    public function can_return_card_expired_card_number()
    {
        $card = $this->gateway->cardExpired();

        $this->assertIsString($card);
        $this->assertSame($card, '4000000000000069');
    }

    /** @test */
    public function can_return_high_risk_card_number()
    {
        $card = $this->gateway->highRisk();

        $this->assertIsString($card);
        $this->assertSame($card, '4000000000004954');
    }
}
