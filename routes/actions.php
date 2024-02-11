<?php

use DuncanMcClean\SimpleCommerce\Http\Controllers\CartController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\CartItemController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\CheckoutController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\CouponController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\CustomerController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\DigitalProducts\DownloadController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\DigitalProducts\VerificationController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\GatewayCallbackController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\GatewayWebhookController;
use DuncanMcClean\SimpleCommerce\Http\Middleware\EnsureFormParametersArriveIntact;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::name('simple-commerce.')->group(function () {
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

    Route::post('/gateways/{gateway}/webhook', [GatewayWebhookController::class, 'index'])
        ->name('gateways.webhook')
        ->withoutMiddleware([VerifyCsrfToken::class]);

    Route::prefix('digital-products')->name('digital-products.')->group(function () {
        Route::get('download/{orderId}/{lineItemId}', DownloadController::class)->name('download');
        Route::post('verification', VerificationController::class)->name('verification');
    });
});
