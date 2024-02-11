<?php

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Orders\Calculator\LineItemTaxCalculator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Pipeline;

uses()->group('calculator');

beforeEach(function () {
    $this->useBasicTaxEngine();
    Config::set('simple-commerce.tax_engine_config.rate', 20);
});

it('calculates tax total when price does not include tax', function () {
    $product = Product::make()->price(1000)->save();

    $order = Order::make()->lineItems([
        ['id' => '123', 'product' => $product->id, 'quantity' => 2, 'total' => 2000],
    ]);

    $order = Pipeline::send($order)->through([LineItemTaxCalculator::class])->thenReturn();

    expect($order->lineItems()->first()->total())->toBe(2000);
    expect($order->lineItems()->first()->tax())->toBe([
        'amount' => 400,
        'rate' => 20,
        'price_includes_tax' => false,
    ]);
    expect($order->taxTotal())->toBe(400);
});

it('calculates tax total when price includes tax', function () {
    Config::set('simple-commerce.tax_engine_config.included_in_prices', true);

    $product = Product::make()->price(1000)->save();

    $order = Order::make()->lineItems([
        ['id' => '123', 'product' => $product->id, 'quantity' => 2, 'total' => 2000],
    ]);

    $order = Pipeline::send($order)->through([LineItemTaxCalculator::class])->thenReturn();

    expect($order->lineItems()->first()->total())->toBe(1667);
    expect($order->lineItems()->first()->tax())->toBe([
        'amount' => 333,
        'rate' => 20,
        'price_includes_tax' => true,
    ]);
    expect($order->taxTotal())->toBe(333);
});
