<?php

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Fieldtypes\GatewayFieldtype;
use DuncanMcClean\SimpleCommerce\Gateways\Builtin\DummyGateway;
use Illuminate\Support\Facades\Auth;
use Statamic\Facades\User;
use Statamic\Fields\Field;

test('can get title', function () {
    $title = GatewayFieldtype::title();

    expect($title)->toBe('Payment Gateway');
});

test('can preload', function () {
    $preload = (new GatewayFieldtype)->preload();

    expect($preload)->toBeArray();
    $this->assertArrayHasKey('gateways', $preload);
});

test('can preprocess', function () {
    $order = Order::make();
    $order->save();

    $user = User::make()->email('test@test.com');
    $user->save();

    $field = new Field('gateway', [
        'type' => 'gateway',
    ]);

    $field->setParent($order->resource());

    Auth::setUser($user);

    $preProcess = (new GatewayFieldtype)->setField($field)->preProcess([
        'use' => DummyGateway::handle(),
        'data' => [
            'id' => 'abc123',
            'smth' => 'cool',
        ],
    ]);

    expect($preProcess)->toBeArray();

    $this->assertArrayHasKey('data', $preProcess);
    $this->assertSame([
        'use' => DummyGateway::handle(),
        'data' => [
            'id' => 'abc123',
            'smth' => 'cool',
        ],
    ], $preProcess['data']);

    $this->assertArrayHasKey('entry', $preProcess);
    $this->assertArrayHasKey('gateway_class', $preProcess);
    $this->assertArrayHasKey('display', $preProcess);
    $this->assertArrayHasKey('actions', $preProcess);
    $this->assertArrayHasKey('action_url', $preProcess);
});

test('can process', function () {
    $process = (new GatewayFieldtype)->process([
        'data' => [
            'use' => DummyGateway::handle(),
            'data' => ['smth' => 'cool'],
        ],
        'actions' => [],
    ]);

    expect($process)->toBeArray();

    $this->assertSame([
        'use' => DummyGateway::handle(),
        'data' => ['smth' => 'cool'],
    ], $process);
});

test('can augment', function () {
    $augment = (new GatewayFieldtype)->augment([
        'use' => DummyGateway::handle(),
        'data' => ['smth' => 'cool'],
    ]);

    expect($augment)->toBeArray();

    $this->assertArrayHasKey('name', $augment);
    $this->assertArrayHasKey('handle', $augment);
    $this->assertArrayHasKey('class', $augment);
    $this->assertArrayHasKey('formatted_class', $augment);
    $this->assertArrayHasKey('display', $augment);
    $this->assertArrayHasKey('checkoutRules', $augment);
    $this->assertArrayHasKey('config', $augment);
    $this->assertArrayHasKey('data', $augment);
});

test('can preprocess index', function () {
    $preProcessIndex = (new GatewayFieldtype)->preProcessIndex([
        'use' => DummyGateway::handle(),
        'data' => ['smth' => 'cool'],
    ]);

    expect($preProcessIndex)->toBe('Dummy');
});
