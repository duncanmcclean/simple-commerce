<?php

use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP\OverviewController;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP\RegionController;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP\TaxCategoryController;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP\TaxRateController;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP\TaxZoneController;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP\VariantFieldtypeController;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Facades\Route;

Route::prefix('simple-commerce')->name('simple-commerce.')->group(function () {
    Route::get('/overview', [OverviewController::class, 'index'])->name('overview');

    if (SimpleCommerce::isUsingStandardTaxEngine()) {
        Route::redirect('tax', 'tax/rates')->name('tax');

        Route::prefix('tax/categories')->name('tax-categories.')->group(function () {
            Route::get('/', [TaxCategoryController::class, 'index'])->name('index');
            Route::get('/create', [TaxCategoryController::class, 'create'])->name('create');
            Route::post('/create', [TaxCategoryController::class, 'store'])->name('store');
            Route::get('/{taxCategory}/edit', [TaxCategoryController::class, 'edit'])->name('edit');
            Route::post('/{taxCategory}/edit', [TaxCategoryController::class, 'update'])->name('update');
            Route::delete('/{taxCategory}/delete', [TaxCategoryController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('tax/rates')->name('tax-rates.')->group(function () {
            Route::get('/', [TaxRateController::class, 'index'])->name('index');
            Route::get('/create', [TaxRateController::class, 'create'])->name('create');
            Route::post('/create', [TaxRateController::class, 'store'])->name('store');
            Route::get('/{taxRate}/edit', [TaxRateController::class, 'edit'])->name('edit');
            Route::post('/{taxRate}/edit', [TaxRateController::class, 'update'])->name('update');
            Route::delete('/{taxRate}/delete', [TaxRateController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('tax/zones')->name('tax-zones.')->group(function () {
            Route::get('/', [TaxZoneController::class, 'index'])->name('index');
            Route::get('/create', [TaxZoneController::class, 'create'])->name('create');
            Route::post('/create', [TaxZoneController::class, 'store'])->name('store');
            Route::get('/{taxZone}/edit', [TaxZoneController::class, 'edit'])->name('edit');
            Route::post('/{taxZone}/edit', [TaxZoneController::class, 'update'])->name('update');
            Route::delete('/{taxZone}/delete', [TaxZoneController::class, 'destroy'])->name('destroy');
        });
    }

    Route::prefix('fieldtype-api')->name('fieldtype-api.')->group(function () {
        Route::get('regions', [RegionController::class, '__invoke'])->name('regions');
        Route::post('product-variant', [VariantFieldtypeController::class, '__invoke'])->name('product-variant');
    });
});
