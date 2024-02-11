<?php

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\Calculator\CalculateGrandTotal;
use Illuminate\Support\Facades\Pipeline;

uses()->group('calculator');

it('calculates the grand total', function () {
    $order = Order::make()
        ->itemsTotal(25000) // 25000 of products
        ->taxTotal(5000) // +5000 cuz of tax
        ->shippingTotal(200) // +200 cuz of shipping
        ->couponTotal(100); // -100 cuz of coupon

    $order = Pipeline::send($order)->through([CalculateGrandTotal::class])->thenReturn();

    expect($order->grandTotal())->toBe(30100);
});
