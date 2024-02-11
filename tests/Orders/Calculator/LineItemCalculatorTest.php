<?php

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Orders\Calculator\LineItemCalculator;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Facades\Pipeline;

uses()->group('calculator');

it('calculates total for standard product', function () {
    $product = Product::make()->price(500)->save();

    $order = Order::make()->lineItems([
        ['id' => '123', 'product' => $product->id, 'quantity' => 1],
    ]);

    $order = Pipeline::send($order)->through([LineItemCalculator::class])->thenReturn();

    expect($order->lineItems()->first()->total())->toBe(500);
});

it('calculates total for standard product with quantity', function () {
    $product = Product::make()->price(500)->save();

    $order = Order::make()->lineItems([
        ['id' => '123', 'product' => $product->id, 'quantity' => 2],
    ]);

    $order = Pipeline::send($order)->through([LineItemCalculator::class])->thenReturn();

    expect($order->lineItems()->first()->total())->toBe(1000);
});

it('calculates total for standard product with product price hook', function () {
    $product = Product::make()->price(500)->save();

    $order = Order::make()->lineItems([
        ['id' => '123', 'product' => $product->id, 'quantity' => 1],
    ]);

    SimpleCommerce::productPriceHook(function ($order, $product) {
        return $product->price() * 2;
    });

    $order = Pipeline::send($order)->through([LineItemCalculator::class])->thenReturn();

    expect($order->lineItems()->first()->total())->toBe(1000);

    // Revert hook
    SimpleCommerce::productPriceHook(function ($order, $product) {
        return $product->price();
    });
});

it('calculates total for standard product and ensures decimals are striped out', function () {
    $product = Product::make()->price('12.99')->save();

    $order = Order::make()->lineItems([
        ['id' => '123', 'product' => $product->id, 'quantity' => 1],
    ]);

    $order = Pipeline::send($order)->through([LineItemCalculator::class])->thenReturn();

    expect($order->lineItems()->first()->total())->toBe(1299);
});

it('calculates total for variant product', function () {
    $product = Product::make()->productVariants([
        'variants' => [
            ['name' => 'Colours', 'values' => ['Red']],
            ['name' => 'Sizes', 'values' => ['Large']],
        ],
        'options' => [
            ['key' => 'Red_Large', 'variant' => 'Red, Large', 'price' => 250],
        ],
    ])->save();

    $order = Order::make()
        ->lineItems([
            [
                'id' => '123',
                'product' => $product->id,
                'variant' => ['product' => $product->id, 'variant' => 'Red_Large'],
                'quantity' => 1,
            ],
        ]);

    $order = Pipeline::send($order)->through([LineItemCalculator::class])->thenReturn();

    expect($order->lineItems()->first()->total())->toBe(250);
});

it('calculates total for variant product with quantity', function () {
    $product = Product::make()->productVariants([
        'variants' => [
            ['name' => 'Colours', 'values' => ['Red']],
            ['name' => 'Sizes', 'values' => ['Large']],
        ],
        'options' => [
            ['key' => 'Red_Large', 'variant' => 'Red, Large', 'price' => 250],
        ],
    ])->save();

    $order = Order::make()
        ->lineItems([
            [
                'id' => '123',
                'product' => $product->id,
                'variant' => ['product' => $product->id, 'variant' => 'Red_Large'],
                'quantity' => 5,
            ],
        ]);

    $order = Pipeline::send($order)->through([LineItemCalculator::class])->thenReturn();

    expect($order->lineItems()->first()->total())->toBe(1250);
});

it('calculates total for variant product with legacy variant format', function () {
    $product = Product::make()->productVariants([
        'variants' => [
            ['name' => 'Colours', 'values' => ['Red']],
            ['name' => 'Sizes', 'values' => ['Large']],
        ],
        'options' => [
            ['key' => 'Red_Large', 'variant' => 'Red, Large', 'price' => 250],
        ],
    ])->save();

    $order = Order::make()
        ->lineItems([
            [
                'id' => '123',
                'product' => $product->id,
                'variant' => 'Red_Large',
                'quantity' => 1,
            ],
        ]);

    $order = Pipeline::send($order)->through([LineItemCalculator::class])->thenReturn();

    expect($order->lineItems()->first()->total())->toBe(250);
});

it('calculates total for variant product with product price hook', function () {
    $product = Product::make()->productVariants([
        'variants' => [
            ['name' => 'Colours', 'values' => ['Red']],
            ['name' => 'Sizes', 'values' => ['Large']],
        ],
        'options' => [
            ['key' => 'Red_Large', 'variant' => 'Red, Large', 'price' => 250],
        ],
    ])->save();

    $order = Order::make()
        ->lineItems([
            [
                'id' => '123',
                'product' => $product->id,
                'variant' => ['product' => $product->id, 'variant' => 'Red_Large'],
                'quantity' => 1,
            ],
        ]);

    SimpleCommerce::productVariantPriceHook(function ($order, $product, $variant) {
        return $variant->price() * 2;
    });

    $order = Pipeline::send($order)->through([LineItemCalculator::class])->thenReturn();

    expect($order->lineItems()->first()->total())->toBe(500);

    // Revert hook
    SimpleCommerce::productVariantPriceHook(function ($order, $product, $variant) {
        return $variant->price();
    });
});

it('calculates total for variant product and ensures decimals are striped out', function () {
    $product = Product::make()->productVariants([
        'variants' => [
            ['name' => 'Colours', 'values' => ['Red']],
            ['name' => 'Sizes', 'values' => ['Large']],
        ],
        'options' => [
            ['key' => 'Red_Large', 'variant' => 'Red, Large', 'price' => 15.50],
        ],
    ])->save();

    $order = Order::make()
        ->lineItems([
            [
                'id' => '123',
                'product' => $product->id,
                'variant' => ['product' => $product->id, 'variant' => 'Red_Large'],
                'quantity' => 1,
            ],
        ]);

    $order = Pipeline::send($order)->through([LineItemCalculator::class])->thenReturn();

    expect($order->lineItems()->first()->total())->toBe(1550);
});
