<?php

use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CartController;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CartItemController;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CheckoutController;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CouponController;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CustomerController;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\ReceiptController;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;

Route::namespace('\DoubleThreeDigital\SimpleCommerce\Http\Controllers\Actions')->name('simple-commerce.')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart', [CartController::class, 'destroy'])->name('cart.empty');

    Route::post('/cart-items', [CartItemController::class, 'store'])->name('cart-items.store');
    Route::post('/cart-items/{item}', [CartItemController::class, 'update'])->name('cart-items.update');
    Route::delete('/cart-items/{item}', [CartItemController::class, 'destroy'])->name('cart-items.destroy');

    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/customer/{customer}', [CustomerController::class, 'index'])->name('customer.index');
    Route::post('/customer/{customer}', [CustomerController::class, 'update'])->name('customer.update');

    Route::post('/coupon', [CouponController::class, 'store'])->name('coupon.store');
    Route::delete('/coupon', [CouponController::class, 'destroy'])->name('coupon.destroy');

    Route::get('/receipt/{orderId}', [ReceiptController::class, 'show'])->name('receipt.show');

    Route::name('gateways.')->prefix('gateways')->group(function () {
        foreach (SimpleCommerce::gateways() as $gateway) {
            Route::get("/{$gateway['handle']}/callback", function () {
                // Get redirect url from tag
                // Redirect there

                // Otherwise return to site homepage with success in session

                return 'You have returned from a gateway';
            })->name("{$gateway['handle']}.callback");

            Route::post("/{$gateway['handle']}/webhook", function () use ($gateway) {
                return \DoubleThreeDigital\SimpleCommerce\Facades\Gateway::use($gateway['class'])->webhook($request);
            })->name("{$gateway['handle']}.webhook");
        }
    });
});
