<?php

use DuncanMcClean\SimpleCommerce\Http\Controllers\CartController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\CartLineItemsController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\CartPaymentGatewaysController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\CartShippingController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\Payments\CheckoutController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\Payments\WebhookController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\StateController;
use Illuminate\Support\Facades\Route;

Route::name('simple-commerce.')->group(function () {
    Route::name('cart.')
        ->prefix('cart')
        ->group(function () {
            Route::get('/', [CartController::class, 'index'])->name('index');
            Route::patch('/', [CartController::class, 'update'])->name('update');
            Route::delete('/', [CartController::class, 'destroy'])->name('destroy');

            Route::post('line-items', [CartLineItemsController::class, 'store'])->name('line-items.store');
            Route::patch('line-items/{lineItem}', [CartLineItemsController::class, 'update'])->name('line-items.update');
            Route::delete('line-items/{lineItem}', [CartLineItemsController::class, 'destroy'])->name('line-items.destroy');

            Route::get('shipping', CartShippingController::class)->name('shipping');
            Route::get('payment-gateways', CartPaymentGatewaysController::class)->name('payment-gateways');
            Route::match(['get', 'post'], 'checkout', CheckoutController::class)->name('checkout');
        });

    Route::name('payments.')
        ->prefix('payments')
        ->withoutMiddleware(['App\Http\Middleware\VerifyCsrfToken', 'Illuminate\Foundation\Http\Middleware\VerifyCsrfToken'])
        ->group(function () {
            Route::post('{paymentGateway}/webhook', WebhookController::class)->name('webhook');
            Route::match(['get', 'post'], '{paymentGateway}/checkout', CheckoutController::class)->name('checkout');
        });

    Route::get('states', StateController::class)->name('states');
});
