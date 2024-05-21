<?php

use Illuminate\Support\Facades\Route;
use DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Orders\OrderController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Orders\OrderActionController;

Route::name('simple-commerce.')->group(function () {
    Route::resource('orders', OrderController::class)->only(['index', 'edit', 'update']);

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::post('actions', [OrderActionController::class, 'run'])->name('actions.run');
        Route::post('actions/list', [OrderActionController::class, 'bulkActions'])->name('actions.bulk');
    });
});
