<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Gateways;

use DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class DummyGatewayTest extends TestCase
{
    public $gateway;

    public function setUp(): void
    {
        parent::setUp();

        $this->gateway = new DummyGateway();
    }

    /** @test */
    public function can_complete_purchase_with_valid_card()
    {
        $completePurchase = $this->gateway->completePurchase([
            'cardholder' => 'Mr Joe Bloggs',
            'cardNumber' => '4242 4242 4242 4242',
            'expiryMonth' => '07',
            'expiryYear' => '2025',
        ]);

        $this->assertIsArray($completePurchase);
        $this->assertArrayHasKey('is_paid', $completePurchase);
        $this->assertSame(true, $completePurchase['is_paid']);
    }

    /** @test */
    public function cant_complete_purchase_with_invalid_card()
    {
        $this->expectExceptionMessage('The card provided is invalid.');

        $completePurchase = $this->gateway->completePurchase([
            'cardholder' => 'Mr Joe Bloggs',
            'cardNumber' => '1111 1111 1111 1111',
            'expiryMonth' => '07',
            'expiryYear' => '2025',
        ]);
    }

    /** @test */
    public function cant_complete_purchase_with_expired_card()
    {
        $completePurchase = $this->gateway->completePurchase([
            'cardholder' => 'Mr Joe Bloggs',
            'cardNumber' => '4242 4242 4242 4242',
            'expiryMonth' => '07',
            'expiryYear' => '2019',
        ]);

        $this->assertIsArray($completePurchase);
        $this->assertArrayHasKey('is_paid', $completePurchase);
        $this->assertSame(false, $completePurchase['is_paid']);
    }

    /** @test */
    public function can_return_validation_rules()
    {
        $rules = $this->gateway->rules();

        $this->assertIsArray($rules);
        $this->assertSame($rules, [
            'cardholder' => 'required|string',
            'cardNumber' => 'required|string',
            'expiryMonth' => 'required',
            'expiryYear' => 'required',
            'cvc' => 'required',
        ]);
    }

    /** @test */
    public function can_return_payment_form()
    {
        $form = $this->gateway->paymentForm();

        // TODO: assert view is returned
    }

    /** @test */
    public function can_issue_refund()
    {
        $refund = $this->gateway->refund([
            'is_paid' => true,
            'cardholder' => 'Mr Joe Bloggs',
            'cardNumber' => '4242 4242 4242 4242',
            'expiryMonth' => '07',
            'expiryYear' => '2019',
            'transaction_id' => uniqid(),
        ]);

        $this->assertIsBool();
        $this->assertSame(true, $refund);
    }

    /** @test */
    public function can_return_name()
    {
        $name = $this->gateway->name();

        $this->assertSame($name, 'Dummy');
    }
}
