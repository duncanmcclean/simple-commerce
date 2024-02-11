<?php

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use DuncanMcClean\SimpleCommerce\Tax\BasicTaxEngine;
use DuncanMcClean\SimpleCommerce\Tax\TaxCalculation;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\ShippingMethods\DummyShippingMethod;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;

/**
 * Inline with the fix suggested here: https://github.com/duncanmcclean/simple-commerce/pull/438#issuecomment-888498198
 */
test('can calculate line item tax when not included in price', function () {
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

    $taxCalculation = (new BasicTaxEngine)->calculateForLineItem($order, $order->lineItems()->first());

    expect($taxCalculation instanceof TaxCalculation)->toBeTrue();

    expect(200)->toBe($taxCalculation->amount());
    expect(false)->toBe($taxCalculation->priceIncludesTax());
    expect(20)->toBe($taxCalculation->rate());
});

test('can calculate line item tax when included in price', function () {
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

    $taxCalculation = (new BasicTaxEngine)->calculateForLineItem($order, $order->lineItems()->first());

    expect($taxCalculation instanceof TaxCalculation)->toBeTrue();

    expect(333)->toBe($taxCalculation->amount());
    expect(true)->toBe($taxCalculation->priceIncludesTax());
    expect(20)->toBe($taxCalculation->rate());
});

test('can calculate line item tax when tax rate is decimal number', function () {
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

    $taxCalculation = (new BasicTaxEngine)->calculateForLineItem($order, $order->lineItems()->first());

    expect($taxCalculation instanceof TaxCalculation)->toBeTrue();

    expect(210)->toBe($taxCalculation->amount());
    expect(false)->toBe($taxCalculation->priceIncludesTax());
    expect(10.5)->toBe($taxCalculation->rate());
});

test('can calculate line item tax when it is nothing', function () {
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

    $taxCalculation = (new BasicTaxEngine)->calculateForLineItem($order, $order->lineItems()->first());

    expect($taxCalculation instanceof TaxCalculation)->toBeTrue();

    expect(0)->toBe($taxCalculation->amount());
    expect(false)->toBe($taxCalculation->priceIncludesTax());
    expect(0)->toBe($taxCalculation->rate());
});

// https://github.com/duncanmcclean/simple-commerce/pull/430
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

    $taxCalculation = (new BasicTaxEngine)->calculateForLineItem($order, $order->lineItems()->first());

    expect($taxCalculation instanceof TaxCalculation)->toBeTrue();

    expect(1300)->toBe($taxCalculation->amount());
    expect(true)->toBe($taxCalculation->priceIncludesTax());
    expect(20)->toBe($taxCalculation->rate());
});

test('can calculate shipping tax when included in price', function () {
    Config::set('simple-commerce.tax_engine_config.rate', 20);
    Config::set('simple-commerce.tax_engine_config.included_in_prices', true);
    Config::set('simple-commerce.tax_engine_config.shipping_taxes', true);

    SimpleCommerce::registerShippingMethod(Site::current()->handle(), DummyShippingMethod::class);

    $order = Order::make()
        ->status(OrderStatus::Cart)
        ->merge(['shipping_method' => DummyShippingMethod::handle()])
        ->shippingTotal(500);

    $order->save();

    $taxCalculation = (new BasicTaxEngine)->calculateForShipping($order, new DummyShippingMethod);

    expect($taxCalculation instanceof TaxCalculation)->toBeTrue();

    expect(100)->toBe($taxCalculation->amount());
    expect(true)->toBe($taxCalculation->priceIncludesTax());
    expect(20)->toBe($taxCalculation->rate());
});
