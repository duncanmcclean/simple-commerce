<?php

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Collection;

uses(TestCase::class);
uses(SetupCollections::class);
beforeEach(function () {
    $this->useBasicTaxEngine();
});


test('can get line items', function () {
    $productOne = Product::make()->price(1000);
    $productOne->save();

    $productTwo = Product::make()->price(1000);
    $productTwo->save();

    $order = Order::make()->lineItems([
        [
            'id' => 'un-doone-two-three-twa',
            'product' => $productOne->id(),
            'quantity' => 2,
        ],
        [
            'id' => 'nine-ten-eleven',
            'product' => $productTwo->id(),
            'quantity' => 2,
        ],
    ]);

    $order->save();

    $lineItems = $order->lineItems();

    $this->assertTrue($lineItems instanceof Collection);
    $this->assertSame($lineItems->count(), 2);
});

test('can get line items when item has to be filtered out to a deleted product', function () {
    $productOne = Product::make()->price(1000);
    $productOne->save();

    $order = Order::make()->lineItems([
        [
            'id' => 'un-doone-two-three-twa',
            'product' => $productOne->id(),
            'quantity' => 2,
        ],
        [
            'id' => 'nine-ten-eleven',
            'product' => 'blah-blah', // this product doesn't exist
            'quantity' => 2,
        ],
    ]);

    $order->save();

    $lineItems = $order->lineItems();

    $this->assertTrue($lineItems instanceof Collection);
    $this->assertSame($lineItems->count(), 1);
});

test('can get line items when item has null product due to a deleted product and paid order', function () {
    $productOne = Product::make()->price(1000);
    $productOne->save();

    $order = Order::make()->status(OrderStatus::Placed)->paymentStatus(PaymentStatus::Paid)->lineItems([
        [
            'id' => 'un-doone-two-three-twa',
            'product' => $productOne->id(),
            'quantity' => 2,
        ],
        [
            'id' => 'nine-ten-eleven',
            'product' => 'blah-blah', // this product doesn't exist
            'quantity' => 2,
        ],
    ]);

    $order->save();

    $lineItems = $order->lineItems();

    $this->assertTrue($lineItems instanceof Collection);
    $this->assertSame($lineItems->count(), 2);
});

test('line items return empty if order has no items', function () {
    $order = Order::make();
    $order->save();

    $lineItems = $order->lineItems();

    $this->assertTrue($lineItems instanceof Collection);
    $this->assertSame($lineItems->count(), 0);
});

test('can update line item', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Four Five Six',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => 'ideeeeee-of-item',
            'product' => $product->id,
            'quantity' => 2,
        ],
    ]);

    $order->save();

    $update = $order->updateLineItem('ideeeeee-of-item', [
        'quantity' => 3,
        'metadata' => [
            'product_key' => 'gday-mate',
        ],
    ]);

    $this->assertSame($order->lineItems()->count(), 1);

    $this->assertSame($order->lineItems()->first()->quantity(), 3);
    $this->assertTrue($order->lineItems()->first()->metadata()->has('product_key'));
});

test('can clear line items', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Four Five Six',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => 'ideeeeee-of-item',
            'product' => $product->id,
            'quantity' => 2,
        ],
    ]);

    $order->save();

    $lineItems = $order->clearlineItems();

    $this->assertTrue($lineItems instanceof Collection);
    $this->assertSame($lineItems->count(), 0);
});
