<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Gateways\Builtin;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Gateways\Builtin\DummyGateway;
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
        $prepare = $this->gateway->prepare(
            new Request(),
            Order::make()
        );

        $this->assertIsArray($prepare);
        $this->assertSame($prepare, []);
    }

    /** @test */
    public function can_checkout()
    {
        Notification::fake();

        TestTime::freeze();

        $checkout = $this->gateway->checkout(
            new Request(),
            Order::make()
        );

        $this->assertIsArray($checkout);

        $this->assertSame([
            'id'        => '123456789abcdefg',
            'last_four' => '4242',
            'date'      => (string) now()->subDays(14),
            'refunded'  => false,
        ], $checkout);
    }

    /** @test */
    public function has_checkout_rules()
    {
        $rules = $this->gateway->checkoutRules();

        $this->assertIsArray($rules);

        $this->assertSame([
            'card_number'   => ['required', 'string'],
            'expiry_month'  => ['required'],
            'expiry_year'   => ['required'],
            'cvc'           => ['required'],
        ], $rules);
    }

    /** @test */
    public function can_refund_charge()
    {
        $refund = $this->gateway->refund(Order::make());

        $this->assertIsArray($refund);
    }
}
