<?php

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Gateways\Builtin\DummyGateway;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Spatie\TestTime\TestTime;
use Statamic\Facades\Collection;

uses(TestCase::class);
beforeEach(function () {
    $this->gateway = new DummyGateway();

    Collection::make('orders')->title('Order')->save();
});


test('has a name', function () {
    $name = $this->gateway->name();

    $this->assertIsString($name);
    $this->assertSame('Dummy', $name);
});

test('can prepare', function () {
    $prepare = $this->gateway->prepare(
        new Request(),
        Order::make()
    );

    $this->assertIsArray($prepare);
    $this->assertSame($prepare, []);
});

test('can checkout', function () {
    Notification::fake();

    TestTime::freeze();

    $checkout = $this->gateway->checkout(
        new Request(),
        Order::make()
    );

    $this->assertIsArray($checkout);

    $this->assertSame([
        'id' => '123456789abcdefg',
        'last_four' => '4242',
        'date' => (string) now()->subDays(14),
        'refunded' => false,
    ], $checkout);
});

test('has checkout rules', function () {
    $rules = $this->gateway->checkoutRules();

    $this->assertIsArray($rules);

    $this->assertSame([
        'card_number' => ['required', 'string'],
        'expiry_month' => ['required'],
        'expiry_year' => ['required'],
        'cvc' => ['required'],
    ], $rules);
});

test('can refund charge', function () {
    $refund = $this->gateway->refund(Order::make());

    $this->assertIsArray($refund);
});
