<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Gateways;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway;
use DoubleThreeDigital\SimpleCommerce\Data\Gateways\GatewayPrep;
use DoubleThreeDigital\SimpleCommerce\Data\Gateways\GatewayPurchase;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Http\Request;
use Spatie\TestTime\TestTime;
use Statamic\Facades\Collection;

class DummyGatewayTest extends TestCase
{
    public $gateway;

    public function setUp(): void
    {
        parent::setUp();

        $this->gateway = new DummyGateway();

        Collection::make('orders')->title('Order')->save();
    }

    /** @test */
    public function has_a_name()
    {
        $name = $this->gateway->name();

        $this->assertIsString($name);
        $this->assertSame('Dummy', $name);
    }

    /** @test */
    public function can_prepare()
    {
        $prepare = $this->gateway->prepare(new GatewayPrep(
            new Request(),
            Cart::make()->save()->entry()
        ));

        $this->assertIsObject($prepare);
        $this->assertTrue($prepare->success());
        $this->assertSame($prepare->data(), []);
    }

    /** @test */
    public function can_purchase()
    {
        TestTime::freeze();

        $purchase = $this->gateway->purchase(new GatewayPurchase(
            new Request(),
            Cart::make()->save()->entry()
        ));

        $this->assertIsObject($purchase);
        $this->assertTrue($purchase->success());
        $this->assertSame([
            'id'        => '123456789abcdefg',
            'last_four' => '4242',
            'date'      => (string) now()->subDays(14),
            'refunded'  => false,
        ], $purchase->data());
    }

    /** @test */
    public function has_purchase_rules()
    {
        $rules = $this->gateway->purchaseRules();

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

        $charge = $this->gateway->getCharge(
            Cart::make()->save()->entry()
        );

        $this->assertIsObject($charge);
        $this->assertSame([
            'id'        => '123456789abcdefg',
            'last_four' => '4242',
            'date'      => (string) now()->subDays(14),
            'refunded'  => false,
        ], $charge->data());
    }

    /** @test */
    public function can_refund_charge()
    {
        $refund = $this->gateway->refundCharge(Cart::make()->save()->entry());

        $this->assertIsObject($refund);
        $this->assertTrue($refund->success());
    }

    /** @test */
    public function can_hit_webhook()
    {
        $webhook = $this->gateway->webhook(new Request());

        $this->assertSame($webhook, null);
    }
}
