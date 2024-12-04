<?php

use DuncanMcClean\SimpleCommerce\Http\Controllers\CP\ConvertGuestToUserController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Coupons\CouponActionController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Coupons\CouponController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Fieldtypes\StateFieldtypeController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Orders\OrderActionController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Orders\OrderController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Orders\DownloadPackingSlipController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Taxes\TaxClassController;
use DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Taxes\TaxZoneController;
use Illuminate\Support\Facades\Route;

Route::name('simple-commerce.')->group(function () {
    Route::resource('coupons', CouponController::class)->except(['show', 'destroy']);
    Route::resource('orders', OrderController::class)->only(['index', 'edit', 'update']);
    Route::resource('tax-classes', TaxClassController::class)->except('show');
    Route::resource('tax-zones', TaxZoneController::class)->except('show');

    Route::prefix('coupons')->name('coupons.')->group(function () {
        Route::post('actions', [CouponActionController::class, 'run'])->name('actions.run');
        Route::post('actions/list', [CouponActionController::class, 'bulkActions'])->name('actions.bulk');
    });

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::post('actions', [OrderActionController::class, 'run'])->name('actions.run');
        Route::post('actions/list', [OrderActionController::class, 'bulkActions'])->name('actions.bulk');

        Route::get('{order}/download-packing-slip', DownloadPackingSlipController::class)->name('download-packing-slip');
    });

    Route::prefix('fieldtypes')->name('fieldtypes.')->group(function () {
        Route::get('states', StateFieldtypeController::class)->name('states');
    });

    Route::post('convert-guest-to-user', ConvertGuestToUserController::class)->name('convert-guest-to-user');
});
