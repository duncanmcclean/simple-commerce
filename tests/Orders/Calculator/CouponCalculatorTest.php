<?php

use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Orders\Calculator\CouponCalculator;
use Illuminate\Support\Facades\Pipeline;

uses()->group('calculator');

it('skips calculating coupon total if order has no coupon', function () {
    $product = Product::make()->price(5000)->save();

    $order = Order::make()->lineItems([
        ['id' => '123', 'product' => $product->id, 'quantity' => 2, 'total' => 10000],
    ])->itemsTotal(10000)->save();

    $order = Pipeline::send($order)->through([CouponCalculator::class])->thenReturn();

    expect($order->couponTotal())->toBe(0);
});

it('skips calculating coupon total if coupon is not valid', function () {
    $product = Product::make()->price(5000)->save();

    $coupon = Coupon::make()
        ->code('fifty-friday')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Fifty Friday',
            'redeemed' => 0,
            'minimum_cart_value' => null,
            'expires_at' => now()->subDay(),
        ])
        ->save();

    $order = Order::make()->lineItems([
        ['id' => '123', 'product' => $product->id, 'quantity' => 2, 'total' => 10000],
    ])->itemsTotal(10000)->coupon($coupon->id)->save();

    $order = Pipeline::send($order)->through([CouponCalculator::class])->thenReturn();

    expect($order->couponTotal())->toBe(0);
});

it('calculates percentage coupon', function () {
    $product = Product::make()->price(5000)->save();

    $coupon = Coupon::make()
        ->code('fifty-friday')
        ->value(50)
        ->type('percentage')
        ->data([
            'description' => 'Fifty Friday',
            'redeemed' => 0,
            'minimum_cart_value' => null,
        ])
        ->save();

    $order = Order::make()->lineItems([
        ['id' => '123', 'product' => $product->id, 'quantity' => 2, 'total' => 10000],
    ])->itemsTotal(10000)->coupon($coupon->id)->save();

    $order = Pipeline::send($order)->through([CouponCalculator::class])->thenReturn();

    expect($order->couponTotal())->toBe(5000);
});

// https://github.com/duncanmcclean/simple-commerce/issues/651
it('calculates percentage coupon when value is a decimal number', function () {
    $product = Product::make()->price(5000)->save();

    $coupon = Coupon::make()
        ->code('fifty-friday')
        ->value('10.00')
        ->type('percentage')
        ->data([
            'description' => 'Fifty Friday',
            'redeemed' => 0,
            'minimum_cart_value' => null,
        ])
        ->save();

    $order = Order::make()->lineItems([
        ['id' => '123', 'product' => $product->id, 'quantity' => 2, 'total' => 10000],
    ])->itemsTotal(10000)->coupon($coupon->id)->save();

    $order = Pipeline::send($order)->through([CouponCalculator::class])->thenReturn();

    expect($order->couponTotal())->toBe(1000);
});

// https://github.com/duncanmcclean/simple-commerce/issues/651
it('calculates percentage coupon when product price has pence', function () {
    $product = Product::make()->price(2499)->save();

    $coupon = Coupon::make()
        ->code('fifty-friday')
        ->value(10)
        ->type('percentage')
        ->data([
            'description' => 'Fifty Friday',
            'redeemed' => 0,
            'minimum_cart_value' => null,
        ])
        ->save();

    $order = Order::make()->lineItems([
        ['id' => '123', 'product' => $product->id, 'quantity' => 1, 'total' => 2499],
    ])->itemsTotal(2499)->coupon($coupon->id)->save();

    $order = Pipeline::send($order)->through([CouponCalculator::class])->thenReturn();

    expect($order->couponTotal())->toBe(250);
});

it('calculates fixed coupon', function () {
    $product = Product::make()->price(5000)->save();

    $coupon = Coupon::make()
        ->code('fifty-friday')
        ->value(100)
        ->type('fixed')
        ->data([
            'description' => 'One Hundred Pence Off (£1)',
            'redeemed' => 0,
            'minimum_cart_value' => null,
        ])
        ->save();

    $order = Order::make()->lineItems([
        ['id' => '123', 'product' => $product->id, 'quantity' => 2, 'total' => 10000],
    ])->itemsTotal(10000)->coupon($coupon->id)->save();

    $order = Pipeline::send($order)->through([CouponCalculator::class])->thenReturn();

    expect($order->couponTotal())->toBe(100);
});

// https://github.com/duncanmcclean/simple-commerce/issues/651
it('calculates fixed coupon when value is a decimal number', function () {
    $product = Product::make()->price(5000)->save();

    $coupon = Coupon::make()
        ->code('fifty-friday')
        ->value('10.00')
        ->type('fixed')
        ->data([
            'description' => 'Ten Pounds Off (£10)',
            'redeemed' => 0,
            'minimum_cart_value' => null,
        ])
        ->save();

    $order = Order::make()->lineItems([
        ['id' => '123', 'product' => $product->id, 'quantity' => 2, 'total' => 10000],
    ])->itemsTotal(10000)->coupon($coupon->id)->save();

    $order = Pipeline::send($order)->through([CouponCalculator::class])->thenReturn();

    expect($order->couponTotal())->toBe(1000);
});
