<?php

use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CartController;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CartItemController;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CheckoutController;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CouponController;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CustomerController;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\GatewayCallbackController;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\GatewayWebhookController;
use DoubleThreeDigital\SimpleCommerce\Http\Middleware\EnsureFormParametersArriveIntact;
use Illuminate\Support\Facades\Route;

Route::namespace('\DoubleThreeDigital\SimpleCommerce\Http\Controllers\Actions')->name('simple-commerce.')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::get('/customer/{customer}', [CustomerController::class, 'index'])->name('customer.index');

    Route::middleware([EnsureFormParametersArriveIntact::class])->group(function () {
        Route::post('/cart', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart', [CartController::class, 'destroy'])->name('cart.empty');

        Route::post('/cart-items', [CartItemController::class, 'store'])->name('cart-items.store');
        Route::post('/cart-items/{item}', [CartItemController::class, 'update'])->name('cart-items.update');
        Route::delete('/cart-items/{item}', [CartItemController::class, 'destroy'])->name('cart-items.destroy');

        Route::post('/checkout', [CheckoutController::class, '__invoke'])->name('checkout.store');

        Route::post('/customer/{customer}', [CustomerController::class, 'update'])->name('customer.update');

        Route::post('/coupon', [CouponController::class, 'store'])->name('coupon.store');
        Route::delete('/coupon', [CouponController::class, 'destroy'])->name('coupon.destroy');
    });

    Route::get('/gateways/{gateway}/callback', [GatewayCallbackController::class, 'index'])->name('gateways.callback');
    Route::post('/gateways/{gateway}/webhook', [GatewayWebhookController::class, 'index'])->name('gateways.webhook');
});
