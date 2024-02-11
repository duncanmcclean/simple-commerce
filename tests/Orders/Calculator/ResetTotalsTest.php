<?php

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Orders\Calculator\ResetTotals;
use Illuminate\Support\Facades\Pipeline;

uses()->group('calculator');

it('resets totals', function () {
    $product = Product::make()->price(1000)->save();

    $order = Order::make()
        ->grandTotal(1400)
        ->itemsTotal(1000)
        ->taxTotal(250)
        ->shippingTotal(250)
        ->couponTotal(100)
        ->lineItems([
            [
                'id' => '123',
                'product' => $product->id,
                'total' => 1000,
                'quantity' => 1,
            ],
        ]);

    $order->save();

    $order = Pipeline::send($order)->through([ResetTotals::class])->thenReturn();

    expect($order->grandTotal())->toBe(0);
    expect($order->itemsTotal())->toBe(0);
    expect($order->taxTotal())->toBe(0);
    expect($order->shippingTotal())->toBe(0);
    expect($order->couponTotal())->toBe(0);
    expect($order->lineItems()->first()->total())->toBe(0);
});
