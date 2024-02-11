<?php

use DuncanMcClean\SimpleCommerce\Actions\RefundAction;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Gateways\Builtin\DummyGateway;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;

beforeEach(function () {
    $this->action = new RefundAction();
});

test('is visible to paid and non refunded order', function () {
    $order = Order::make()->status(OrderStatus::Placed)->paymentStatus(PaymentStatus::Paid);
    $order->save();

    $action = $this->action->visibleTo($order->resource());

    expect($action)->toBeTrue();
})->skip();

test('is not visible to unpaid orders', function () {
    $order = Order::make()->status(OrderStatus::Cart)->paymentStatus(PaymentStatus::Unpaid);
    $order->save();

    $action = $this->action->visibleTo($order->resource());

    expect($action)->toBeFalse();
})->skip();

test('is not visible to already refunded orders', function () {
    $order = Order::make()->paymentStatus(PaymentStatus::Refunded);

    $order->save();

    $action = $this->action->visibleTo($order->resource());

    expect($action)->toBeFalse();
})->skip();

test('is not visible to products', function () {
    $product = Product::make()
        ->price(1200)
        ->data([
            'title' => 'Medium Jumper',
        ]);

    $product->save();

    $action = $this->action->visibleTo($product->resource());

    expect($action)->toBeFalse();
})->skip();

test('is not able to be run in bulk', function () {
    $order = Order::make()->paymentStatus(PaymentStatus::Refunded);

    $order->save();

    $action = $this->action->visibleToBulk([$order->resource()]);

    expect($action)->toBeFalse();
})->skip();

test('order can be refunded', function () {
    Collection::make('orders')->save();

    $order = Entry::make()
        ->collection('orders')
        ->id(Stache::generateId())
        ->merge([
            'status' => OrderStatus::Placed,
            'payment_status' => PaymentStatus::Paid,
            'gateway' => [
                'use' => DummyGateway::handle(),
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
