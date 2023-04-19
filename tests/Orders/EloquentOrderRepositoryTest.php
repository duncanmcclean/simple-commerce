<?php

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderModel;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\UseDatabaseContentDrivers;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

uses(SetupCollections::class);
uses(RefreshDatabase::class);
uses(UseDatabaseContentDrivers::class);

test('can get all orders', function () {
    $productOne = Product::make()->price(1000);
    $productOne->save();

    $productTwo = Product::make()->price(1000);
    $productTwo->save();

    OrderModel::create([
        'items' => [
            [
                'product' => $productOne->id(),
                'quantity' => 1,
                'total' => 1000,
            ],
        ],
        'data' => [
            'foo' => 'bar',
        ],
    ]);

    OrderModel::create([
        'items' => [
            [
                'product' => $productTwo->id(),
                'quantity' => 1,
                'total' => 1000,
            ],
        ],
        'data' => [
            'boo' => 'foo',
        ],
    ]);

    $all = Order::all();

    expect($all instanceof Collection)->toBeTrue();
    expect(2)->toBe($all->count());
});

test('can find order', function () {
    $product = Product::make()->price(1000);
    $product->save();

    $order = OrderModel::create([
        'items' => [
            [
                'product' => $product->id(),
                'quantity' => 1,
                'total' => 1000,
            ],
        ],
        'data' => [
            'foo' => 'bar',
        ],
    ]);

    $find = Order::find($order->id);

    expect($order->id)->toBe($find->id());
    expect(1)->toBe($find->lineItems()->count());
    expect('bar')->toBe($find->get('foo'));
});

test('can find order with custom column', function () {
    $product = Product::make()->price(1000);
    $product->save();

    $order = OrderModel::create([
        'items' => [
            [
                'product' => $product->id(),
                'quantity' => 1,
                'total' => 1000,
            ],
        ],
        'data' => [
            'foo' => 'bar',
        ],
        'ordered_on_tuesday' => 'Yes',
    ]);

    $find = Order::find($order->id);

    expect($order->id)->toBe($find->id());
    expect(1)->toBe($find->lineItems()->count());
    expect('bar')->toBe($find->get('foo'));
    expect('Yes')->toBe($find->get('ordered_on_tuesday'));
});

test('can create', function () {
    $create = Order::make()
        ->status(OrderStatus::Placed)
        ->paymentStatus(PaymentStatus::Paid)
        ->grandTotal(1000);

    $create->save();

    $this->assertNotNull($create->id());
    expect(OrderStatus::Placed)->toBe($create->status());
    expect(PaymentStatus::Paid)->toBe($create->paymentStatus());
    expect(1000)->toBe($create->grandTotal());
});

test('can save', function () {
    $product = Product::make()->price(1000);
    $product->save();

    $orderRecord = OrderModel::create([
        'items' => [
            [
                'product' => $product->id(),
                'quantity' => 1,
                'total' => 1000,
            ],
        ],
        'data' => [
            'foo' => 'bar',
        ],
    ]);

    $order = Order::find($orderRecord->id);

    $order->set('is_special_order', true);

    $order->save();

    expect($orderRecord->id)->toBe($order->id());
    expect(true)->toBe($order->get('is_special_order'));
});

test('can save when bit of data has its own column', function () {
    $product = Product::make()->price(1000);
    $product->save();

    $orderRecord = OrderModel::create([
        'items' => [
            [
                'product' => $product->id(),
                'quantity' => 1,
                'total' => 1000,
            ],
        ],
    ]);

    $order = Order::find($orderRecord->id);

    $order->set('ordered_on_tuesday', 'Yes');

    $order->save();

    expect($orderRecord->id)->toBe($order->id());
    expect('Yes')->toBe($order->get('ordered_on_tuesday'));

    $this->assertDatabaseHas('orders', [
        'id' => $orderRecord->id,
        'ordered_on_tuesday' => 'Yes',
    ]);
});

test('can delete', function () {
    $orderRecord = OrderModel::create([
        'payment_status' => 'paid',
        'grand_total' => 1000,
    ]);

    $order = Order::find($orderRecord->id);

    $order->delete();

    $this->assertDatabaseMissing('orders', [
        'id' => $orderRecord->id,
        'payment_status' => 'paid',
        'grand_total' => 1000,
    ]);
});
