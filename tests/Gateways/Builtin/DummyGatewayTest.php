<?php

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Gateways\Builtin\DummyGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Spatie\TestTime\TestTime;
use Statamic\Facades\Collection;

beforeEach(function () {
    $this->gateway = new DummyGateway();

    Collection::make('orders')->title('Order')->save();
});

test('has a name', function () {
    $name = $this->gateway->name();

    expect($name)->toBeString();
    expect($name)->toBe('Dummy');
});

test('can prepare', function () {
    $prepare = $this->gateway->prepare(
        new Request(),
        Order::make()
    );

    expect($prepare)->toBeArray();
    expect([])->toBe($prepare);
});

test('can checkout', function () {
    Notification::fake();

    TestTime::freeze();

    $checkout = $this->gateway->checkout(
        new Request(),
        Order::make()
    );

    expect($checkout)->toBeArray();

    $this->assertSame([
        'id' => '123456789abcdefg',
        'last_four' => '4242',
        'date' => (string) now()->subDays(14),
        'refunded' => false,
    ], $checkout);
});

test('has checkout rules', function () {
    $rules = $this->gateway->checkoutRules();

    expect($rules)->toBeArray();

    $this->assertSame([
        'card_number' => ['required', 'string'],
        'expiry_month' => ['required'],
        'expiry_year' => ['required'],
        'cvc' => ['required'],
    ], $rules);
});

test('can refund charge', function () {
    $refund = $this->gateway->refund(Order::make());

    expect($refund)->toBeArray();
});
