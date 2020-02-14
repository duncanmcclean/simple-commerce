<?php

Route::namespace('\DoubleThreeDigital\SimpleCommerce\Http\Controllers\Web')->group(function () {
    Route::get(config('simple-commerce.cart_index'), 'CartController@index')->name('cart.index');
    Route::post(config('simple-commerce.cart_store'), 'CartController@store')->name('cart.add');
    Route::post(config('simple-commerce.cart_clear'), 'ClearCartController')->name('cart.clear');
    Route::post(config('simple-commerce.cart_remove'), 'CartController@destroy')->name('cart.delete');

    Route::get(config('simple-commerce.checkout_show'), 'CheckoutController@show')->name('checkout.show');
    Route::post(config('simple-commerce.checkout_store'), 'CheckoutController@store')->name('checkout.store');

    Route::get(config('simple-commerce.product_index'), 'ProductController@index')->name('products.index');
    Route::get(config('simple-commerce.product_search'), 'ProductSearchController@index')->name('products.search');
    Route::get(config('simple-commerce.product_search').'/results', 'ProductSearchController@show')->name('products.search.results');
    Route::get(config('simple-commerce.product_show'), 'ProductController@show')->name('products.show');

    Route::get(config('simple-commerce.categories_show'), 'ProductCategoryController@show')->name('categories.show');
});
