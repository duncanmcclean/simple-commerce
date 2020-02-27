<?php

if (! function_exists('sc_route')) {
    function sc_route(string $key) {
        return config("simple-commerce.routes.$key");
    }
}

Route::namespace('\DoubleThreeDigital\SimpleCommerce\Http\Controllers\Web')->group(function () {
    Route::get(sc_route('cart_index'), 'CartController@index')->name('cart.index');
    Route::post(sc_route('cart_store'), 'CartController@store')->name('cart.add');
    Route::post(sc_route('cart_clear'), 'ClearCartController')->name('cart.clear');
    Route::post(sc_route('cart_remove'), 'CartController@destroy')->name('cart.delete');

    Route::get(sc_route('checkout_show'), 'CheckoutController@show')->name('checkout.show');
    Route::post(sc_route('checkout_store'), 'CheckoutController@store')->name('checkout.store');

    Route::get(sc_route('product_index'), 'ProductController@index')->name('products.index');
    Route::get(sc_route('product_search'), 'ProductSearchController@index')->name('products.search');
    Route::get(sc_route('product_search').'/results', 'ProductSearchController@show')->name('products.search.results');
    Route::get(sc_route('product_show'), 'ProductController@show')->name('products.show');

    Route::get(sc_route('categories_show'), 'ProductCategoryController@show')->name('categories.show');
});
