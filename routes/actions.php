<?php

use DuncanMcClean\SimpleCommerce\Http\Controllers\CartController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\CartLineItemsController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\CheckoutController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\DigitalProducts\DownloadController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\Payments\CallbackController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\Payments\WebhookController;
use DuncanMcClean\SimpleCommerce\Http\Middleware\EnsureFormParametersArriveIntact;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::name('simple-commerce.')->group(function () {
    Route::middleware(EnsureFormParametersArriveIntact::class)->group(function () {
        Route::get('cart', [CartController::class, 'index'])->name('cart.index');
        Route::patch('cart', [CartController::class, 'update'])->name('cart.update');
        Route::delete('cart', [CartController::class, 'destroy'])->name('cart.update');

        Route::post('cart/line-items', [CartLineItemsController::class, 'store'])->name('cart.line-items.store');
        Route::patch('cart/line-items/{lineItem}', [CartLineItemsController::class, 'update'])->name('cart.line-items.update');
        Route::delete('cart/line-items/{lineItem}', [CartLineItemsController::class, 'destroy'])->name('cart.line-items.destroy');

        Route::post('checkout', CheckoutController::class)->name('checkout');
    });

    Route::name('payments.')
        ->prefix('payments')
        ->withoutMiddleware(VerifyCsrfToken::class)
        ->group(function () {
            Route::get('{gateway}/callback', CallbackController::class)->name('callback');
            Route::post('{gateway}/webhook', WebhookController::class)->name('webhook');
        });

    Route::name('digital-products')
        ->prefix('digital-products')
        ->group(function () {
            Route::get('download/{order}/{lineItem}', DownloadController::class)->name('download');
        });
});