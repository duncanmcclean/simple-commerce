<?php

Route::namespace('Http\Controllers\Cp')->group(function () {
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

        Route::get('/{order}/{status}', 'OrderStatusController@update')->name('.status-update');
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
    });

});
