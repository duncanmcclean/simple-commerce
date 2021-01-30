<?php

use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP\VariantFieldtypeController;
use Illuminate\Support\Facades\Route;

Route::prefix('simple-commerce')->name('simple-commerce.')->group(function () {
    Route::prefix('fieldtype-api')->name('fieldtype-api.')->group(function () {
        Route::post('product-variant', [VariantFieldtypeController::class, '__invoke'])->name('product-variant');
    });
});
