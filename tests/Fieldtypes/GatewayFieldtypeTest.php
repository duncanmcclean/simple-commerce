<?php

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Fieldtypes\GatewayFieldtype;
use DoubleThreeDigital\SimpleCommerce\Gateways\Builtin\DummyGateway;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Statamic\Facades\User;
use Statamic\Fields\Field;

uses(TestCase::class);

test('can get title', function () {
    $title = GatewayFieldtype::title();

    $this->assertSame('Payment Gateway', $title);
});

test('can preload', function () {
    $preload = (new GatewayFieldtype)->preload();

    $this->assertIsArray($preload);
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
        'use' => DummyGateway::class,
        'data' => [
            'id' => 'abc123',
            'smth' => 'cool',
        ],
    ]);

    $this->assertIsArray($preProcess);

    $this->assertArrayHasKey('data', $preProcess);
    $this->assertSame([
        'use' => DummyGateway::class,
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
            'use' => DummyGateway::class,
            'data' => ['smth' => 'cool'],
        ],
        'actions' => [],
    ]);

    $this->assertIsArray($process);

    $this->assertSame([
        'use' => DummyGateway::class,
        'data' => ['smth' => 'cool'],
    ], $process);
});

test('can augment', function () {
    $augment = (new GatewayFieldtype)->augment([
        'use' => DummyGateway::class,
        'data' => ['smth' => 'cool'],
    ]);

    $this->assertIsArray($augment);

    $this->assertArrayHasKey('name', $augment);
    $this->assertArrayHasKey('handle', $augment);
    $this->assertArrayHasKey('class', $augment);
    $this->assertArrayHasKey('formatted_class', $augment);
    $this->assertArrayHasKey('display', $augment);
    $this->assertArrayHasKey('checkoutRules', $augment);
    $this->assertArrayHasKey('config', $augment);
    $this->assertArrayHasKey('webhook_url', $augment);
    $this->assertArrayHasKey('data', $augment);
});

test('can preprocess index', function () {
    $preProcessIndex = (new GatewayFieldtype)->preProcessIndex([
        'use' => DummyGateway::class,
        'data' => ['smth' => 'cool'],
    ]);

    $this->assertSame('Dummy', $preProcessIndex);
});
