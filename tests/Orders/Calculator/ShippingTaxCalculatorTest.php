<?php

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Orders\Calculator\ShippingTaxCalculator;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\ShippingMethods\Postage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Pipeline;
use Statamic\Facades\Site;

uses()->group('calculator');

beforeEach(function () {
    $this->useBasicTaxEngine();
    Config::set('simple-commerce.tax_engine_config.rate', 20);
    Config::set('simple-commerce.tax_engine_config.shipping_taxes', true);
});

it('does not calculate shipping tax without a shipping method', function () {
    $product = Product::make()->price(1000);
    $product->save();

    $order = Order::make()->lineItems([
        ['id' => '123', 'product' => $product->id, 'quantity' => 1, 'total' => 1000],
    ])->save();

    $order = Pipeline::send($order)->through([ShippingTaxCalculator::class])->thenReturn();

    expect($order->get('shipping_tax'))->toBeNull();
    expect($order->taxTotal())->toBe(0);
});

it('calculates shipping tax when price does not include tax', function () {
    SimpleCommerce::registerShippingMethod(Site::current()->handle(), Postage::class);

    $product = Product::make()->price(1000);
    $product->save();

    $order = Order::make()->lineItems([
        ['id' => '123', 'product' => $product->id, 'quantity' => 1, 'total' => 1000],
    ])->set('shipping_method', Postage::handle())->shippingTotal(250)->save();

    $order = Pipeline::send($order)->through([ShippingTaxCalculator::class])->thenReturn();

    expect($order->get('shipping_tax'))->toBeArray()->toBe([
        'amount' => 50,
        'rate' => 20,
        'price_includes_tax' => false,
    ]);
    expect($order->taxTotal())->toBe(50);
    expect($order->shippingTotal())->toBe(250);
});

it('calculates shipping tax when price includes tax', function () {
    Config::set('simple-commerce.tax_engine_config.included_in_prices', true);
    SimpleCommerce::registerShippingMethod(Site::current()->handle(), Postage::class);

    $product = Product::make()->price(1000);
    $product->save();

    $order = Order::make()->lineItems([
        ['id' => '123', 'product' => $product->id, 'quantity' => 1, 'total' => 1000],
    ])->set('shipping_method', Postage::handle())->shippingTotal(250)->save();

    $order = Pipeline::send($order)->through([ShippingTaxCalculator::class])->thenReturn();

    expect($order->get('shipping_tax'))->toBeArray()->toBe([
        'amount' => 50,
        'rate' => 20,
        'price_includes_tax' => true,
    ]);
    expect($order->taxTotal())->toBe(50);
    expect($order->shippingTotal())->toBe(200);
});
