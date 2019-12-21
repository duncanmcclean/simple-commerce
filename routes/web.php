<?php

Route::get('/products', 'Http\Controllers\Web\ProductController@index');
Route::get('/products/search', 'Http\Controllers\Web\ProductSearchController@index');
Route::get('/products/search/results', 'Http\Controllers\Web\ProductSearchController@show');
Route::get('/products/{product}', 'Http\Controllers\Web\ProductController@show');
Route::post('/cart', 'Http\Controllers\Web\CartController@store');
Route::post('/cart/clear', 'Http\Controllers\Web\ClearCartController');
Route::post('/cart/delete', 'Http\Controllers\Web\CartController@destroy');
Route::get('/checkout', 'Http\Controllers\Web\CheckoutController@show');
Route::post('/checkout', 'Http\Controllers\Web\CheckoutController@store');
Route::get('/thanks', 'Http\Controllers\Web\ThanksController');
