<?php

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Tax\BasicTaxEngine;
use DoubleThreeDigital\SimpleCommerce\Tax\TaxCalculation;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Collection;

uses(TestCase::class);

/**
 * Inline with the fix suggested here: https://github.com/duncanmcclean/simple-commerce/pull/438#issuecomment-888498198
 */
test('can calculate tax when not included in price', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 20);
    Config::set('simple-commerce.tax_engine_config.included_in_prices', false);

    $product = Product::make()->price(1000);
    $product->save();

    $order = Order::make()->status(OrderStatus::Cart)->lineItems([
        $lineItem = [
            'product' => $product->id,
            'quantity' => 1,
            'total' => 1000,
        ],
    ]);

    $order->save();

    $taxCalculation = (new BasicTaxEngine)->calculate($order, $lineItem);

    $this->assertTrue($taxCalculation instanceof TaxCalculation);

    $this->assertSame($taxCalculation->amount(), 200);
    $this->assertSame($taxCalculation->priceIncludesTax(), false);
    $this->assertSame($taxCalculation->rate(), 20);
});

test('can calculate tax when included in price', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 20);
    Config::set('simple-commerce.tax_engine_config.included_in_prices', true);

    $product = Product::make()->price(1000);
    $product->save();

    $order = Order::make()->status(OrderStatus::Cart)->lineItems([
        $lineItem = [
            'product' => $product->id,
            'quantity' => 2,
            'total' => 2000,
        ],
    ]);

    $order->save();

    $taxCalculation = (new BasicTaxEngine)->calculate($order, $lineItem);

    $this->assertTrue($taxCalculation instanceof TaxCalculation);

    $this->assertSame($taxCalculation->amount(), 333);
    $this->assertSame($taxCalculation->priceIncludesTax(), true);
    $this->assertSame($taxCalculation->rate(), 20);
});

test('can calculate tax when tax rate is decimal number', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 10.5);

    Collection::make('products')->save();
    Collection::make('orders')->save();

    $product = Product::make()->price(1000);
    $product->save();

    $order = Order::make()->status(OrderStatus::Cart)->lineItems([
        $lineItem = [
            'product' => $product->id,
            'quantity' => 2,
            'total' => 2000,
        ],
    ]);

    $order->save();

    $taxCalculation = (new BasicTaxEngine)->calculate($order, $lineItem);

    $this->assertTrue($taxCalculation instanceof TaxCalculation);

    $this->assertSame($taxCalculation->amount(), 210);
    $this->assertSame($taxCalculation->priceIncludesTax(), false);
    $this->assertSame($taxCalculation->rate(), 10.5);
});

test('can calculate tax when it is nothing', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 0);

    $product = Product::make()->price(1000);
    $product->save();

    $order = Order::make()->status(OrderStatus::Cart)->lineItems([
        $lineItem = [
            'product' => $product->id,
            'quantity' => 2,
            'total' => 2000,
        ],
    ]);

    $order->save();

    $taxCalculation = (new BasicTaxEngine)->calculate($order, $lineItem);

    $this->assertTrue($taxCalculation instanceof TaxCalculation);

    $this->assertSame($taxCalculation->amount(), 0);
    $this->assertSame($taxCalculation->priceIncludesTax(), false);
    $this->assertSame($taxCalculation->rate(), 0);
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
        $lineItem = [
            'product' => $product->id,
            'quantity' => 3,
            'total' => 7800,
        ],
    ]);

    $order->save();

    $taxCalculation = (new BasicTaxEngine)->calculate($order, $lineItem);

    $this->assertTrue($taxCalculation instanceof TaxCalculation);

    $this->assertSame($taxCalculation->amount(), 1300);
    $this->assertSame($taxCalculation->priceIncludesTax(), true);
    $this->assertSame($taxCalculation->rate(), 20);
});
