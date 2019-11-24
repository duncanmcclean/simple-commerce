<?php

Route::prefix('products')->as('products')->group(function() {
    Route::get('/', 'Http\Controllers\ProductController@index')->name('.index');
    Route::get('/search', 'Http\Controllers\ProductSearchController')->name('.search');
    Route::get('/create', 'Http\Controllers\ProductController@create')->name('.create');
    Route::post('/create', 'Http\Controllers\ProductController@store')->name('.store');
    Route::get('/edit/{product}', 'Http\Controllers\ProductController@edit')->name('.edit');
    Route::post('/edit/{product}', 'Http\Controllers\ProductController@update')->name('.update');
    Route::get('/delete/{product}', 'Http\Controllers\ProductController@destroy')->name('.destroy');
});

Route::prefix('orders')->as('orders')->group(function() {
    Route::get('/', 'Http\Controllers\OrderController@index')->name('.index');
    //Route::get('/search', 'Http\Controllers\OrderSearchController')->name('.search');
    Route::get('/create', 'Http\Controllers\OrderController@create')->name('.create');
    Route::post('/create', 'Http\Controllers\OrderController@store')->name('.store');
    Route::get('/edit/{order}', 'Http\Controllers\OrderController@edit')->name('.edit');
    Route::post('/edit/{order}', 'Http\Controllers\OrderController@update')->name('.update');
    Route::get('/delete/{order}', 'Http\Controllers\OrderController@destroy')->name('.destroy');
});

Route::prefix('coupons')->as('coupons')->group(function() {
    Route::get('/', 'Http\Controllers\CouponController@index')->name('.index');
    //Route::get('/search', 'Http\Controllers\CouponSearchController')->name('.search');
    Route::get('/create', 'Http\Controllers\CouponController@create')->name('.create');
    Route::post('/create', 'Http\Controllers\CouponController@store')->name('.store');
    Route::get('/edit/{coupon}', 'Http\Controllers\CouponController@edit')->name('.edit');
    Route::post('/edit/{coupon}', 'Http\Controllers\CouponController@update')->name('.update');
    Route::get('/delete/{coupon}', 'Http\Controllers\CouponController@destroy')->name('.destroy');
});

Route::prefix('customer')->as('customers')->group(function() {
    Route::get('/', 'Http\Controllers\CustomerController@index')->name('.index');
    //Route::get('/search', 'Http\Controllers\CustomerSearchController')->name('.search');
    Route::get('/create', 'Http\Controllers\CustomerController@create')->name('.create');
    Route::post('/create', 'Http\Controllers\CustomerController@store')->name('.store');
    Route::get('/edit/{customer}', 'Http\Controllers\CustomerController@edit')->name('.edit');
    Route::post('/edit/{customer}', 'Http\Controllers\CustomerController@update')->name('.update');
    Route::get('/delete/{customer}', 'Http\Controllers\CustomerController@destroy')->name('.destroy');
});
