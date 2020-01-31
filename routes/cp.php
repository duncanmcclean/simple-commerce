<?php

Route::namespace('\DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp')->group(function () {
    Route::prefix('products')->as('products')->group(function () {
        Route::get('/', 'ProductController@index')->name('.index');
        Route::get('/create', 'ProductController@create')->name('.create');
        Route::post('/create', 'ProductController@store')->name('.store');
        Route::get('/edit/{product}', 'ProductController@edit')->name('.edit');
        Route::post('/edit/{product}', 'ProductController@update')->name('.update');
        Route::get('/delete/{product}', 'ProductController@destroy')->name('.destroy');
    });

    Route::prefix('product-categories')->as('product-categories')->group(function () {
        Route::get('/', 'ProductCategoryController@index')->name('.index');
        Route::get('/create', 'ProductCategoryController@create')->name('.create');
        Route::post('/create', 'ProductCategoryController@store')->name('.store');
        Route::get('/{category}', 'ProductCategoryController@show')->name('.show');
        Route::get('/edit/{category}', 'ProductCategoryController@edit')->name('.edit');
        Route::post('/edit/{category}', 'ProductCategoryController@update')->name('.update');
        Route::get('/delete/{category}', 'ProductCategoryController@destroy')->name('.destroy');
    });

    Route::prefix('orders')->as('orders')->group(function () {
        Route::get('/', 'OrderController@index')->name('.index');
        Route::get('/edit/{order}', 'OrderController@edit')->name('.edit');
        Route::post('/edit/{order}', 'OrderController@update')->name('.update');
        Route::get('/delete/{order}', 'OrderController@destroy')->name('.destroy');

        Route::get('/{order}/{status}', 'UpdateOrderStatusController')->name('.status-update');
    });

    Route::prefix('customers')->as('customers')->group(function () {
        Route::get('/', 'CustomerController@index')->name('.index');
        Route::get('/create', 'CustomerController@create')->name('.create');
        Route::post('/create', 'CustomerController@store')->name('.store');
        Route::get('/edit/{customer}', 'CustomerController@edit')->name('.edit');
        Route::post('/edit/{customer}', 'CustomerController@update')->name('.update');
        Route::get('/delete/{customer}', 'CustomerController@destroy')->name('.destroy');
    });

    Route::prefix('settings')->as('settings')->group(function () {
        Route::get('/', 'SettingsController@edit')->name('.edit');
        Route::post('/', 'SettingsController@update')->name('.update');
    });

    Route::prefix('commerce-api')->as('commerce-api')->group(function () {
        Route::post('/customer-order', 'CustomerOrderController@index')->name('.customer-order');
        Route::get('/refund-order/{order}', 'RefundOrderController@store')->name('.refund-order');

        Route::get('/order-status', 'OrderStatusController@index')->name('.order-status.index');
        Route::post('/order-status/create', 'OrderStatusController@store')->name('.order-status.store');
        Route::post('/order-status/{status}', 'OrderStatusController@update')->name('.order-status.update');
        Route::get('/order-status/{status}', 'OrderStatusController@destroy')->name('.order-status.destroy');

        Route::get('/tax-rates', 'TaxRateController@index')->name('.tax-rates.index');
        Route::post('/tax-rates/create', 'TaxRateController@store')->name('.tax-rates.store');
        Route::post('/tax-rates/{rate}', 'TaxRateController@index')->name('.tax-rates.update');
        Route::get('/tax-rates/{rate}', 'TaxRateController@destroy')->name('.tax-rates.destroy');

        Route::get('/shipping-zones', 'ShippingZoneController@index')->name('.shipping-zones.index');
        Route::post('/shipping-zones/create', 'ShippingZoneController@store')->name('.shipping-zones.store');
        Route::post('/shipping-zones/{zone}', 'ShippingZoneController@index')->name('.shipping-zones.update');
        Route::get('/shipping-zones/{zone}', 'ShippingZoneController@destroy')->name('.shipping-zones.destroy');
    });

});
