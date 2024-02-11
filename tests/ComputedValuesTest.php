<?php

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Support\Carbon;

uses(DuncanMcClean\SimpleCommerce\Tests\TestCase::class);
uses(SetupCollections::class);

test('product returns with raw price value', function () {
    $product = Product::make()->price(1500);
    $product->save();

    expect($product->resource()->raw_price)->toBe(1500);
});

test('order returns with order date value', function () {
    $order = Order::make()->set('status_log', [
        ['status' => 'placed', 'timestamp' => Carbon::parse('1st January 2023')->timestamp, 'data' => []],
    ]);
    $order->save();

    expect($order->resource()->order_date->format('Y-m-d'))->toBe('2023-01-01');
});
