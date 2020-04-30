<?php

Route::namespace('\DoubleThreeDigital\SimpleCommerce\Http\Controllers\Actions')->name('simple-commerce.')->group(function () {
    Route::post('/cart/create', 'CartController@store')->name('cart.store');
    Route::post('/cart/update', 'CartController@update')->name('cart.update');
    Route::post('/cart/delete', 'CartController@destroy')->name('cart.destroy');

    Route::post('/checkout', 'CheckoutController@store')->name('checkout.store');
    Route::post('/redeem-coupon', 'RedeemCouponController')->name('redeem-coupon');
});
