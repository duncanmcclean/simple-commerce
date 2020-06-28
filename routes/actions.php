<?php

use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CartController;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CheckoutController;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CouponController;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CustomerController;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\ShippingOptionController;

Route::namespace('\DoubleThreeDigital\SimpleCommerce\Http\Controllers\Actions')->name('simple-commerce.')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::post('/cart/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{item}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/cart/empty', [CartController::class, 'destroy'])->name('cart.empty');

    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/customer', [CustomerController::class, 'show'])->name('customer.show');
    Route::post('/customer/update', [CustomerController::class, 'update'])->name('customer.update');

    Route::get('/shipping-options', [ShippingOptionController::class, 'index'])->name('shipping-options.index');
    Route::post('/shipping-options', [ShippingOptionController::class, 'update'])->name('shipping-options.update');

    Route::get('/gateways', [ShippingOptionController::class, 'index'])->name('gateways.index');
    Route::get('/gateways/{gateway}', [ShippingOptionController::class, 'show'])->name('gateways.show');

    Route::post('/coupon', [CouponController::class, 'store'])->name('coupon.store');
    Route::delete('/coupon', [CouponController::class, 'destroy'])->name('coupon.destroy');
});