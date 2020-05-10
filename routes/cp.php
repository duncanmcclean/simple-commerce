<?php

use DoubleThreeDigital\SimpleCommerce\Http\Middleware\AccessSettings;

Route::namespace('\DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp')->group(function () {
    Route::prefix('products')->as('products')->group(function () {
        Route::get('/', 'ProductController@index')->name('.index');
        Route::get('/create', 'ProductController@create')->name('.create');
        Route::post('/create', 'ProductController@store')->name('.store');
        Route::get('/edit/{product}', 'ProductController@edit')->name('.edit');
        Route::post('/edit/{product}', 'ProductController@update')->name('.update');
        Route::get('/duplicate/{product}', 'DuplicateProductController')->name('.duplicate');
        Route::delete('/delete/{product}', 'ProductController@destroy')->name('.destroy');
    });

    Route::prefix('product-categories')->as('product-categories')->group(function () {
        Route::get('/', 'ProductCategoryController@index')->name('.index');
        Route::get('/create', 'ProductCategoryController@create')->name('.create');
        Route::post('/create', 'ProductCategoryController@store')->name('.store');
        Route::get('/{category}', 'ProductCategoryController@show')->name('.show');
        Route::get('/edit/{category}', 'ProductCategoryController@edit')->name('.edit');
        Route::post('/edit/{category}', 'ProductCategoryController@update')->name('.update');
        Route::delete('/delete/{category}', 'ProductCategoryController@destroy')->name('.destroy');
    });

    Route::prefix('orders')->as('orders')->group(function () {
        Route::get('/', 'OrderController@index')->name('.index');
        Route::get('/edit/{order}', 'OrderController@edit')->name('.edit');
        Route::post('/edit/{order}', 'OrderController@update')->name('.update');
        Route::get('/refund/{order}', 'RefundOrderController@store')->name('.refund');
        Route::get('/status/{order}/{status}', 'UpdateOrderStatusController@update')->name('.status');
        Route::delete('/delete/{order}', 'OrderController@destroy')->name('.destroy');
    });

    Route::prefix('coupons')->as('coupons')->group(function () {
        Route::get('/', 'CouponController@index')->name('.index');
        Route::get('/create', 'CouponController@create')->name('.create');
        Route::post('/create', 'CouponController@store')->name('.store');
        Route::get('/edit/{coupon}', 'CouponController@edit')->name('.edit');
        Route::post('/edit/{coupon}', 'CouponController@update')->name('.update');
        Route::delete('/delete/{coupon}', 'CouponController@destroy')->name('.destroy');
    });

    Route::prefix('settings')->as('settings')->middleware(AccessSettings::class)->group(function () {
        Route::get('/', 'Settings\SettingsHomeController@index')->name('.index');
        Route::get('/order-statuses', 'Settings\OrderStatusController@index')->name('.order-statuses.index');
        Route::get('/tax-rates', 'Settings\TaxRateController@index')->name('.tax-rates.index');
        Route::get('/shipping', 'Settings\ShippingController@index')->name('.shipping.index');
    });

    Route::prefix('order-status')->as('order-status')->middleware(AccessSettings::class)->group(function () {
        Route::get('/', 'OrderStatusController@index')->name('.index');
        Route::post('/create', 'OrderStatusController@store')->name('.store');
        Route::post('/{status}', 'OrderStatusController@update')->name('.update');
        Route::delete('/{status}', 'OrderStatusController@destroy')->name('.destroy');
    });

    Route::prefix('tax-rates')->as('tax-rates')->middleware(AccessSettings::class)->group(function () {
        Route::get('/', 'TaxRateController@index')->name('.index');
        Route::post('/create', 'TaxRateController@store')->name('.store');
        Route::post('/{rate}', 'TaxRateController@update')->name('.update');
        Route::delete('/{rate}', 'TaxRateController@destroy')->name('.destroy');
    });

    Route::prefix('shipping-zones')->as('shipping-zones')->middleware(AccessSettings::class)->group(function () {
        Route::get('/', 'ShippingZoneController@index')->name('.index');
        Route::post('/create', 'ShippingZoneController@store')->name('.store');
        Route::get('/{zone}', 'ShippingZoneController@edit')->name('.edit');
        Route::post('/{zone}', 'ShippingZoneController@update')->name('.update');
        Route::delete('/{zone}', 'ShippingZoneController@destroy')->name('.destroy');
    });

    Route::post('/fieldtype/customer-orders', 'CustomerOrderController')->name('fieldtype-data.customer-orders');
});
