<?php

use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;

Route::namespace('Http\Controllers\Web')->group(function () {
    Route::get(config('commerce.cart_index'), 'CartController@index')->name('cart.index');
    Route::post(config('commerce.cart_store'), 'CartController@store')->name('cart.add');
    Route::post(config('commerce.cart_clear'), 'ClearCartController')->name('cart.clear');
    Route::post(config('commerce.cart_remove'), 'CartController@destroy')->name('cart.delete');

    Route::get(config('commerce.checkout_show'), 'CheckoutController@show')->name('checkout.show');
    Route::post(config('commerce.checkout_store'), 'CheckoutController@store')->name('checkout.store');

    Route::get(config('commerce.product_index'), 'ProductController@index')->name('products.index');
    Route::get(config('commerce.product_search'), 'ProductSearchController@index')->name('products.search');
    Route::get(config('commerce.product_search').'/results', 'ProductSearchController@show')->name('products.search.results');

//    ProductCategory::all()
//        ->each(function ($category) {
//            Route::get($category->category_route, 'ProductCategoryController@show')->name("categories.{$category->slug}");
//
//            collect(Product::where('product_category_id', $category->id)->get())
//                ->each(function ($product) use ($category) {
//                    Route::get($category->product_route, 'ProductController@show')->name("products.{$category->slug}.show");
//                });
//        });
});
