<?php

use DoubleThreeDigital\SimpleCommerce\Actions\UpdateOrderStatus;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use Spatie\TestTime\TestTime;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;

beforeEach(function () {
    $this->setupCollections();

    $this->action = new UpdateOrderStatus();
});


test('is not visible to products', function () {
    $product = Product::make()
        ->price(1200)
        ->data([
            'title' => 'Medium Jumper',
        ]);

    $product->save();

    $action = $this->action->visibleTo($product->resource());

    expect($action)->toBeFalse();
});

test('order can have its status updated', function () {
    TestTime::freeze();

    $now = TestTime::now()->format('Y-m-d H:i');

    Collection::make('orders')->save();

    $order = Entry::make()
        ->collection('orders')
        ->id(Stache::generateId())
        ->data([
            'order_status' => 'cart',
        ]);

    $order->save();

    $this->action->run([$order], [
        'order_status' => 'dispatched',
    ]);

    $order->fresh();

    expect('dispatched')->toBe($order->data()->get('order_status'));

    $this->assertSame($order->data()->get('status_log'), [
        'dispatched' => $now,
    ]);
});
