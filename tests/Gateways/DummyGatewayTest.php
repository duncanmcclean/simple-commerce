<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Gateways;

use DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Spatie\TestTime\TestTime;

class DummyGatewayTest extends TestCase
{
    /** @test */
    public function has_a_name()
    {
        $name = (new DummyGateway)->name();

        $this->assertSame('Dummy', $name);
    }

    /** @test */
    public function can_prepare()
    {
        $prepare = (new DummyGateway)->prepare([]);

        $this->assertIsArray($prepare);
        $this->assertSame([], $prepare);
    }

    /** @test */
    public function can_purchase()
    {
        TestTime::freeze();

        $purchase = (new DummyGateway)->purchase([
            'card_number' => '4242 4242 4242 4242',
        ]);

        $this->assertIsArray($purchase);
        $this->assertSame([
            'id'        => '123456789abcdefg',
            'last_four' => '4242',
            'date'      => (string) now()->subDays(14),
            'refunded'  => false,
        ], $purchase);
    }

    // /** @test */
    // public function cant_purchase_with_invalid_card()
    // {
    //     $purchase = (new DummyGateway)->purchase([
    //         'card_number' => '1212 1212 1212 1212',
    //     ]);

    //     $this->assertNull($purchase);
    // }

    /** @test */
    public function has_purchase_rules()
    {
        $rules = (new DummyGateway)->purchaseRules();

        $this->assertIsArray($rules);
        $this->assertSame([
            'card_number'   => 'required|string',
            'expiry_month'  => 'required',
            'expiry_year'   => 'required',
            'cvc'           => 'required',
        ], $rules);
    }

    /** @test */
    public function can_get_charge()
    {
        TestTime::freeze();

        $charge = (new DummyGateway)->getCharge([]); // Most of the time, we'll pass in an entry, but we'll just keep it empty here

        $this->assertIsArray($charge);
        $this->assertSame([
            'id'        => '123456789abcdefg',
            'last_four' => '4242',
            'date'      => (string) now()->subDays(14),
            'refunded'  => false,
        ], $charge);
    }

    /** @test */
    public function can_refund_charge()
    {
        $refund = (new DummyGateway)->refundCharge([]);

        $this->assertIsArray($refund);
        $this->assertSame([], $refund);
    }
}