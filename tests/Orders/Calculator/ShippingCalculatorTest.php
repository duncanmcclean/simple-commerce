<?php

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Orders\Calculator\ShippingCalculator;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Tests\Fixtures\ShippingMethods\Postage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Pipeline;
use Statamic\Facades\Site;

uses()->group('calculator');

it('does not calculate shipping total without default shipping method or shipping method on the order', function () {
    SimpleCommerce::registerShippingMethod(Site::current()->handle(), Postage::class);
    Config::set('simple-commerce.sites.'.Site::current()->handle().'.shipping.default_method', null);

    $product = Product::make()->price(1000);
    $product->save();

    $order = Order::make()->lineItems([
        ['id' => '123', 'product' => $product->id, 'quantity' => 2, 'total' => 2000],
    ])->save();

    $order = Pipeline::send($order)->through([ShippingCalculator::class])->thenReturn();

    expect($order->shippingTotal())->toBe(0);
});

it('calculates shipping total using default shipping method', function () {
    SimpleCommerce::registerShippingMethod(Site::current()->handle(), Postage::class);
    Config::set('simple-commerce.sites.'.Site::current()->handle().'.shipping.default_method', Postage::class);

    $product = Product::make()->price(1000);
    $product->save();

    $order = Order::make()->lineItems([
        ['id' => '123', 'product' => $product->id, 'quantity' => 2, 'total' => 2000],
    ])->save();

    $order = Pipeline::send($order)->through([ShippingCalculator::class])->thenReturn();

    expect($order->shippingTotal())->toBe(250);
});

it('calculates shipping total using shipping method from order', function () {
    SimpleCommerce::registerShippingMethod(Site::current()->handle(), Postage::class);

    $product = Product::make()->price(1000);
    $product->save();

    $order = Order::make()->lineItems([
        ['id' => '123', 'product' => $product->id, 'quantity' => 2, 'total' => 2000],
    ])->set('shipping_method', Postage::class)->save();

    $order = Pipeline::send($order)->through([ShippingCalculator::class])->thenReturn();

    expect($order->shippingTotal())->toBe(250);
});
