<?php

use DoubleThreeDigital\SimpleCommerce\Http\Middleware\AccessSettings;

Route::namespace('\DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp')->group(function () {
    Route::prefix('products')->as('products')->group(function () {
        Route::get('/', 'ProductController@index')->name('.index');
        Route::get('/create', 'ProductController@create')->name('.create');
        Route::post('/create', 'ProductController@store')->name('.store');
        Route::get('/edit/{product}', 'ProductController@edit')->name('.edit');
        Route::post('/edit/{product}', 'ProductController@update')->name('.update');
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
        Route::delete('/delete/{order}', 'OrderController@destroy')->name('.destroy');

        Route::get('/{order}/{status}', 'UpdateOrderStatusController')->name('.status-update');
    });

    Route::prefix('customers')->as('customers')->group(function () {
        Route::get('/', 'CustomerController@index')->name('.index');
        Route::get('/create', 'CustomerController@create')->name('.create');
        Route::post('/create', 'CustomerController@store')->name('.store');
        Route::get('/edit/{customer}', 'CustomerController@edit')->name('.edit');
        Route::post('/edit/{customer}', 'CustomerController@update')->name('.update');
        Route::delete('/delete/{customer}', 'CustomerController@destroy')->name('.destroy');
    });

    Route::prefix('settings')->as('settings')->middleware(AccessSettings::class)->group(function () {
        Route::get('/', 'Settings\SettingsHomeController@index')->name('.index');
        Route::get('/order-statuses', 'Settings\OrderStatusController@index')->name('.order-statuses.index');
        Route::get('/tax-rates', 'Settings\TaxRateController@index')->name('.tax-rates.index');
        Route::get('/shipping-zones', 'Settings\ShippingZoneController@index')->name('.shipping-zones.index');
    });

    Route::prefix('commerce-api')->as('commerce-api')->group(function () {
        Route::post('/customer-order', 'API\CustomerOrderController@index')->name('.customer-order');
        Route::get('/refund-order/{order}', 'RefundOrderController@store')->name('.refund-order');

        Route::middleware(AccessSettings::class)->group(function () {
            Route::get('/order-status', 'API\OrderStatusController@index')->name('.order-status.index');
            Route::post('/order-status/create', 'API\OrderStatusController@store')->name('.order-status.store');
            Route::post('/order-status/{status}', 'API\OrderStatusController@update')->name('.order-status.update');
            Route::delete('/order-status/{status}', 'API\OrderStatusController@destroy')->name('.order-status.destroy');

            Route::get('/tax-rates', 'API\TaxRateController@index')->name('.tax-rates.index');
            Route::post('/tax-rates/create', 'API\TaxRateController@store')->name('.tax-rates.store');
            Route::post('/tax-rates/{rate}', 'API\TaxRateController@index')->name('.tax-rates.update');
            Route::get('/tax-rates/{rate}', 'API\TaxRateController@destroy')->name('.tax-rates.destroy');

            Route::get('/shipping-zones', 'API\ShippingZoneController@index')->name('.shipping-zones.index');
            Route::post('/shipping-zones/create', 'API\ShippingZoneController@store')->name('.shipping-zones.store');
            Route::post('/shipping-zones/{zone}', 'API\ShippingZoneController@index')->name('.shipping-zones.update');
            Route::get('/shipping-zones/{zone}', 'API\ShippingZoneController@destroy')->name('.shipping-zones.destroy');
        });
    });

});
