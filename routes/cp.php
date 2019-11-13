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
