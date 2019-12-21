<?php

Route::get('/commerce', 'Http\Controllers\Cp\DashboardController')->name('commerce.dashboard');

Route::prefix('products')->as('products')->group(function() {
    Route::get('/', 'Http\Controllers\Cp\ProductController@index')->name('.index');
    Route::get('/search', 'Http\Controllers\Cp\ProductSearchController')->name('.search');
    Route::get('/create', 'Http\Controllers\Cp\ProductController@create')->name('.create');
    Route::post('/create', 'Http\Controllers\Cp\ProductController@store')->name('.store');
    Route::get('/edit/{product}', 'Http\Controllers\Cp\ProductController@edit')->name('.edit');
    Route::post('/edit/{product}', 'Http\Controllers\Cp\ProductController@update')->name('.update');
    Route::get('/delete/{product}', 'Http\Controllers\Cp\ProductController@destroy')->name('.destroy');
});

Route::prefix('orders')->as('orders')->group(function() {
    Route::get('/', 'Http\Controllers\Cp\OrderController@index')->name('.index');
    Route::get('/search', 'Http\Controllers\Cp\OrderSearchController')->name('.search');
    Route::get('/create', 'Http\Controllers\Cp\OrderController@create')->name('.create');
    Route::post('/create', 'Http\Controllers\Cp\OrderController@store')->name('.store');
    Route::get('/edit/{order}', 'Http\Controllers\Cp\OrderController@edit')->name('.edit');
    Route::post('/edit/{order}', 'Http\Controllers\Cp\OrderController@update')->name('.update');
    Route::get('/delete/{order}', 'Http\Controllers\Cp\OrderController@destroy')->name('.destroy');
});

Route::prefix('coupons')->as('coupons')->group(function() {
    Route::get('/', 'Http\Controllers\Cp\CouponController@index')->name('.index');
    Route::get('/search', 'Http\Controllers\Cp\CouponSearchController')->name('.search');
    Route::get('/create', 'Http\Controllers\Cp\CouponController@create')->name('.create');
    Route::post('/create', 'Http\Controllers\Cp\CouponController@store')->name('.store');
    Route::get('/edit/{coupon}', 'Http\Controllers\Cp\CouponController@edit')->name('.edit');
    Route::post('/edit/{coupon}', 'Http\Controllers\Cp\CouponController@update')->name('.update');
    Route::get('/delete/{coupon}', 'Http\Controllers\Cp\CouponController@destroy')->name('.destroy');
});

Route::prefix('customers')->as('customers')->group(function() {
    Route::get('/', 'Http\Controllers\Cp\CustomerController@index')->name('.index');
    Route::get('/search', 'Http\Controllers\Cp\CustomerSearchController')->name('.search');
    Route::get('/create', 'Http\Controllers\Cp\CustomerController@create')->name('.create');
    Route::post('/create', 'Http\Controllers\Cp\CustomerController@store')->name('.store');
    Route::get('/edit/{customer}', 'Http\Controllers\Cp\CustomerController@edit')->name('.edit');
    Route::post('/edit/{customer}', 'Http\Controllers\Cp\CustomerController@update')->name('.update');
    Route::get('/delete/{customer}', 'Http\Controllers\Cp\CustomerController@destroy')->name('.destroy');
});
