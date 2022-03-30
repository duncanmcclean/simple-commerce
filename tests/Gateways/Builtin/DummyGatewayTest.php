<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Gateways\Builtin;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Gateways\Builtin\DummyGateway;
use DoubleThreeDigital\SimpleCommerce\Gateways\Prepare;
use DoubleThreeDigital\SimpleCommerce\Gateways\Purchase;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
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
        $prepare = $this->gateway->prepare(new Prepare(
            new Request(),
            Order::make()
        ));

        $this->assertIsObject($prepare);
        $this->assertTrue($prepare->success());
        $this->assertSame($prepare->data(), []);
    }

    /** @test */
    public function can_purchase()
    {
        Notification::fake();

        TestTime::freeze();

        $purchase = $this->gateway->purchase(new Purchase(
            new Request(),
            Order::make()
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
            Order::make()
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
        $refund = $this->gateway->refundCharge(Order::make());

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
