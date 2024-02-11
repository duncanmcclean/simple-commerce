<?php

use DuncanMcClean\SimpleCommerce\Exceptions\CheckoutProductHasNoStockException;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Orders\Checkout\ValidateProductStock;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Pipeline\Pipeline;

uses(SetupCollections::class);

test('can pass validation for standard product with enough stock', function () {
    $product = Product::make()
        ->price(1200)
        ->stock(10)
        ->data([
            'title' => 'Medium Jumper',
        ]);

    $product->save();

    $order = Order::make()
        ->lineItems([
            [
                'product' => $product->id(),
                'quantity' => 3,
            ],
        ]);

    $order->save();

    try {
        app(Pipeline::class)
            ->send($order)
            ->through([ValidateProductStock::class])
            ->thenReturn();

        // No exception was thrown, so we're good.
        expect(true)->toBeTrue();
    } catch (CheckoutProductHasNoStockException $e) {
        $this->fail('Validation failed when it should have passed.');
    }
});

test('cant pass validation for standard product without enough stock to fulfill order', function () {
    $product = Product::make()
        ->price(1200)
        ->stock(5)
        ->data([
            'title' => 'Giant Jumper',
        ]);

    $product->save();

    $order = Order::make()
        ->lineItems([
            [
                'product' => $product->id(),
                'quantity' => 20,
            ],
        ]);

    $order->save();

    try {
        app(Pipeline::class)
            ->send($order)
            ->through([ValidateProductStock::class])
            ->thenReturn();

        $this->fail('Validation passed when it should have failed.');
    } catch (CheckoutProductHasNoStockException $e) {
        expect($order->lineItems()->count() === 0)->toBeTrue();
    }
});

test('cant pass validation for standard product with no stock', function () {
    $product = Product::make()
        ->price(1200)
        ->stock(0)
        ->data([
            'title' => 'Tiny Jumper',
        ]);

    $product->save();

    $order = Order::make()
        ->lineItems([
            [
                'product' => $product->id(),
                'quantity' => 5,
            ],
        ]);

    $order->save();

    try {
        app(Pipeline::class)
            ->send($order)
            ->through([ValidateProductStock::class])
            ->thenReturn();

        $this->fail('Validation passed when it should have failed.');
    } catch (CheckoutProductHasNoStockException $e) {
        expect($order->lineItems()->count() === 0)->toBeTrue();
    }
});

test('can pass validation for variant product with enough stock', function () {
    $product = Product::make()
        ->productVariants([
            'variants' => [
                [
                    'name' => 'Colour',
                    'values' => ['Yellow'],
                ],
                [
                    'name' => 'Size',
                    'values' => ['Large'],
                ],
            ],
            'options' => [
                [
                    'key' => 'Yellow_Large',
                    'variant' => 'Yellow, Large',
                    'price' => 1500,
                    'stock' => 10,
                ],
            ],
        ]);

    $product->save();

    $order = Order::make()
        ->lineItems([
            [
                'product' => $product->id(),
                'variant' => 'Yellow_Large',
                'quantity' => 3,
            ],
        ]);

    $order->save();

    try {
        app(Pipeline::class)
            ->send($order)
            ->through([ValidateProductStock::class])
            ->thenReturn();

        // No exception was thrown, so we're good.
        expect(true)->toBeTrue();
    } catch (CheckoutProductHasNoStockException $e) {
        $this->fail('Validation failed when it should have passed.');
    }
});

test('cant pass validation for variant product without enough stock to fulfill order', function () {
    $product = Product::make()
        ->productVariants([
            'variants' => [
                [
                    'name' => 'Colour',
                    'values' => ['Yellow'],
                ],
                [
                    'name' => 'Size',
                    'values' => ['Large'],
                ],
            ],
            'options' => [
                [
                    'key' => 'Yellow_Large',
                    'variant' => 'Yellow, Large',
                    'price' => 1500,
                    'stock' => 10,
                ],
            ],
        ]);

    $product->save();

    $order = Order::make()
        ->lineItems([
            [
                'product' => $product->id(),
                'variant' => 'Yellow_Large',
                'quantity' => 25,
            ],
        ]);

    $order->save();

    try {
        app(Pipeline::class)
            ->send($order)
            ->through([ValidateProductStock::class])
            ->thenReturn();

        $this->fail('Validation passed when it should have failed.');
    } catch (CheckoutProductHasNoStockException $e) {
        expect($order->lineItems()->count() === 0)->toBeTrue();
    }
});

test('cant pass validation for variant product with no stock', function () {
    $product = Product::make()
        ->productVariants([
            'variants' => [
                [
                    'name' => 'Colour',
                    'values' => ['Yellow'],
                ],
                [
                    'name' => 'Size',
                    'values' => ['Large'],
                ],
            ],
            'options' => [
                [
                    'key' => 'Yellow_Large',
                    'variant' => 'Yellow, Large',
                    'price' => 1500,
                    'stock' => 0,
                ],
            ],
        ]);

    $product->save();

    $order = Order::make()
        ->lineItems([
            [
                'product' => $product->id(),
                'variant' => 'Yellow_Large',
                'quantity' => 5,
            ],
        ]);

    $order->save();

    try {
        app(Pipeline::class)
            ->send($order)
            ->through([ValidateProductStock::class])
            ->thenReturn();

        $this->fail('Validation passed when it should have failed.');
    } catch (CheckoutProductHasNoStockException $e) {
        expect($order->lineItems()->count() === 0)->toBeTrue();
    }
});
