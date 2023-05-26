<?php

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Tax\BasicTaxEngine;
use DoubleThreeDigital\SimpleCommerce\Tax\TaxCalculation;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Collection;

/**
 * Inline with the fix suggested here: https://github.com/duncanmcclean/simple-commerce/pull/438#issuecomment-888498198
 */
test('can calculate tax when not included in price', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 20);
    Config::set('simple-commerce.tax_engine_config.included_in_prices', false);

    $product = Product::make()->price(1000);
    $product->save();

    $order = Order::make()->status(OrderStatus::Cart)->lineItems([
        [
            'product' => $product->id,
            'quantity' => 1,
            'total' => 1000,
        ],
    ]);

    $order->save();

    $taxCalculation = (new BasicTaxEngine)->calculate($order, $order->lineItems()->first());

    expect($taxCalculation instanceof TaxCalculation)->toBeTrue();

    expect(200)->toBe($taxCalculation->amount());
    expect(false)->toBe($taxCalculation->priceIncludesTax());
    expect(20)->toBe($taxCalculation->rate());
});

test('can calculate tax when included in price', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 20);
    Config::set('simple-commerce.tax_engine_config.included_in_prices', true);

    $product = Product::make()->price(1000);
    $product->save();

    $order = Order::make()->status(OrderStatus::Cart)->lineItems([
        [
            'product' => $product->id,
            'quantity' => 2,
            'total' => 2000,
        ],
    ]);

    $order->save();

    $taxCalculation = (new BasicTaxEngine)->calculate($order, $order->lineItems()->first());

    expect($taxCalculation instanceof TaxCalculation)->toBeTrue();

    expect(333)->toBe($taxCalculation->amount());
    expect(true)->toBe($taxCalculation->priceIncludesTax());
    expect(20)->toBe($taxCalculation->rate());
});

test('can calculate tax when tax rate is decimal number', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 10.5);

    Collection::make('products')->save();
    Collection::make('orders')->save();

    $product = Product::make()->price(1000);
    $product->save();

    $order = Order::make()->status(OrderStatus::Cart)->lineItems([
        [
            'product' => $product->id,
            'quantity' => 2,
            'total' => 2000,
        ],
    ]);

    $order->save();

    $taxCalculation = (new BasicTaxEngine)->calculate($order, $order->lineItems()->first());

    expect($taxCalculation instanceof TaxCalculation)->toBeTrue();

    expect(210)->toBe($taxCalculation->amount());
    expect(false)->toBe($taxCalculation->priceIncludesTax());
    expect(10.5)->toBe($taxCalculation->rate());
});

test('can calculate tax when it is nothing', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 0);

    $product = Product::make()->price(1000);
    $product->save();

    $order = Order::make()->status(OrderStatus::Cart)->lineItems([
        [
            'product' => $product->id,
            'quantity' => 2,
            'total' => 2000,
        ],
    ]);

    $order->save();

    $taxCalculation = (new BasicTaxEngine)->calculate($order, $order->lineItems()->first());

    expect($taxCalculation instanceof TaxCalculation)->toBeTrue();

    expect(0)->toBe($taxCalculation->amount());
    expect(false)->toBe($taxCalculation->priceIncludesTax());
    expect(0)->toBe($taxCalculation->rate());
});

/**
 * Covers #430 (https://github.com/duncanmcclean/simple-commerce/pull/430)
 */
test('ensure round value tax is calculated correctly', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 20);
    Config::set('simple-commerce.tax_engine_config.included_in_prices', true);

    $product = Product::make()->price(2600);
    $product->save();

    $order = Order::make()->status(OrderStatus::Cart)->lineItems([
        [
            'product' => $product->id,
            'quantity' => 3,
            'total' => 7800,
        ],
    ]);

    $order->save();

    $taxCalculation = (new BasicTaxEngine)->calculate($order, $order->lineItems()->first());

    expect($taxCalculation instanceof TaxCalculation)->toBeTrue();

    expect(1300)->toBe($taxCalculation->amount());
    expect(true)->toBe($taxCalculation->priceIncludesTax());
    expect(20)->toBe($taxCalculation->rate());
});
