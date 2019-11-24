<?php

use Statamic\View\View;
use Facades\Damcclean\Commerce\Models\Product;
use Illuminate\Http\Request;

Route::get('/products', function() {
    return (new View)
        ->template('commerce.products')
        ->layout('layout')
        ->with([]);
});

Route::get('/products/{product}', function(Request $request, $product) {
    $product = Product::get($product);

    return (new View)
        ->template('commerce.product')
        ->layout('layout')
        ->with((array) $product);
});

Route::post('/cart', function() {
    // add item to cart
    // redirect back to page with 'added to cart' message
});

Route::get('/cart', function() {
    // Display items currently in the users cart
});

Route::get('/checkout', function() {
    // Display the list of items in the users cart
    // Allow the user to input their payment information
});

Route::post('/checkout', function() {
    // process the payment information to create stripe order & customer
    // create order and customer in addon
    // Send a notification to the customer and the store owner
    // Present user with success message
});
