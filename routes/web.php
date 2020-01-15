<?php

use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;

Route::get(config('commerce.routes.thanks'), 'Http\Controllers\Web\ThanksController')->name('thanks');
//Route::post(config('commerce.routes.redeem_coupon'), 'Http\Controllers\Web\RedeemCouponController')->name('coupon.redeem');

Route::get(config('commerce.routes.cart.index'), 'Http\Controllers\Web\CartController@index')->name('cart.index');
Route::post(config('commerce.routes.cart.add'), 'Http\Controllers\Web\CartController@store')->name('cart.add');
Route::post(config('commerce.routes.cart.clear'), 'Http\Controllers\Web\ClearCartController')->name('cart.clear');
Route::post(config('commerce.routes.cart.delete'), 'Http\Controllers\Web\CartController@destroy')->name('cart.delete');

Route::get(config('commerce.routes.checkout.show'), 'Http\Controllers\Web\CheckoutController@show')->name('checkout.show');
Route::post(config('commerce.routes.checkout.store'), 'Http\Controllers\Web\CheckoutController@store')->name('checkout.store');

Route::get(config('commerce.routes.products.index'), 'Http\Controllers\Web\ProductController@index')->name('products.index');
Route::get(config('commerce.routes.products.search'), 'Http\Controllers\Web\ProductSearchController@index')->name('products.search');
Route::get(config('commerce.routes.products.search').'/results', 'Http\Controllers\Web\ProductSearchController@show')->name('products.search.results');

collect(ProductCategory::all())
    ->each(function ($category) {
        Route::get($category->category_route, 'Http\Controllers\Web\ProductCategoryController@show')->name('categories.show');

        $products = Product::where('product_category_id', $category->od);

        collect($products)
            ->each(function ($product) use ($category) {
                Route::get($category->product_route, 'Http\Controllers\Web\ProductController@show')->name('products.show');
            });
    });
