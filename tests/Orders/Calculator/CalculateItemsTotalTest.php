<?php

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Orders\Calculator\CalculateItemsTotal;
use Illuminate\Support\Facades\Pipeline;

uses()->group('calculator');

it('calculates items total', function () {
    $productA = Product::make()->price(1000)->save();
    $productB = Product::make()->price(6789)->save();

    $order = Order::make()->lineItems([
        ['id' => 'abc', 'product' => $productA->id, 'quantity' => 2, 'total' => 2000],
        ['id' => '123', 'product' => $productB->id, 'quantity' => 1, 'total' => 6789],
    ]);

    $order = Pipeline::send($order)->through([CalculateItemsTotal::class])->thenReturn();

    expect($order->itemsTotal())->toBe(8789);
});
