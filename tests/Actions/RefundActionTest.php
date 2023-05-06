<?php

use DoubleThreeDigital\SimpleCommerce\Actions\RefundAction;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;

beforeEach(function () {
    $this->action = new RefundAction();
});

test('is visible to paid and non refunded order', function () {
    $this->markTestSkipped();

    $order = Order::make()->status(OrderStatus::Placed)->paymentStatus(PaymentStatus::Paid);
    $order->save();

    $action = $this->action->visibleTo($order->resource());

    expect($action)->toBeTrue();
});

test('is not visible to unpaid orders', function () {
    $this->markTestSkipped();

    $order = Order::make()->status(OrderStatus::Cart)->paymentStatus(PaymentStatus::Unpaid);
    $order->save();

    $action = $this->action->visibleTo($order->resource());

    expect($action)->toBeFalse();
});

test('is not visible to already refunded orders', function () {
    $this->markTestSkipped();

    $order = Order::make()->paymentStatus(PaymentStatus::Refunded);

    $order->save();

    $action = $this->action->visibleTo($order->resource());

    expect($action)->toBeFalse();
});

test('is not visible to products', function () {
    $this->markTestSkipped();

    $product = Product::make()
        ->price(1200)
        ->data([
            'title' => 'Medium Jumper',
        ]);

    $product->save();

    $action = $this->action->visibleTo($product->resource());

    expect($action)->toBeFalse();
});

test('is not able to be run in bulk', function () {
    $this->markTestSkipped();

    $order = Order::make()->paymentStatus(PaymentStatus::Refunded);

    $order->save();

    $action = $this->action->visibleToBulk([$order->resource()]);

    expect($action)->toBeFalse();
});

test('order can be refunded', function () {
    Collection::make('orders')->save();

    $order = Entry::make()
        ->collection('orders')
        ->id(Stache::generateId())
        ->merge([
            'status' => OrderStatus::Placed,
            'payment_status' => PaymentStatus::Paid,
            'gateway' => [
                'use' => 'DoubleThreeDigital\SimpleCommerce\Gateways\Builtin\DummyGateway',
                'data' => [
                    'id' => '123456789abcdefg',
                ],
            ],
        ]);

    $order->save();

    $this->action->run([$order], null);

    $order->fresh();

    expect('refunded')->toBe($order->data()->get('payment_status'));
    $this->assertArrayHasKey('refund', $order->data()->get('gateway'));
});
