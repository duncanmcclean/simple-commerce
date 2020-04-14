<?php

Route::namespace('\DoubleThreeDigital\SimpleCommerce\Http\Controllers\Actions')->name('simple-commerce.')->group(function () {
    Route::post('/cart/update', 'CartController@update')->name('cart.update');

    Route::post('/checkout', 'CheckoutController@store')->name('checkout.store');
});
