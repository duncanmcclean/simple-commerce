<?php

use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP\TaxCategoryController;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP\VariantFieldtypeController;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Facades\Route;

Route::prefix('simple-commerce')->name('simple-commerce.')->group(function () {
    if (SimpleCommerce::isUsingStandardTaxEngine()) {
        Route::prefix('tax-categories')->name('tax-categories.')->group(function () {
            Route::get('/', [TaxCategoryController::class, 'index'])->name('index');
            Route::get('/create', [TaxCategoryController::class, 'create'])->name('create');
            Route::post('/create', [TaxCategoryController::class, 'store'])->name('store');
            Route::get('/{taxCategory}/edit', [TaxCategoryController::class, 'edit'])->name('edit');
            Route::post('/{taxCategory}/edit', [TaxCategoryController::class, 'update'])->name('update');
            Route::delete('/{taxCategory}/delete', [TaxCategoryController::class, 'destroy'])->name('destroy');
        });
    }

    Route::prefix('fieldtype-api')->name('fieldtype-api.')->group(function () {
        Route::post('product-variant', [VariantFieldtypeController::class, '__invoke'])->name('product-variant');
    });
});
