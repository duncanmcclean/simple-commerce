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

    // WIP don't allow here if the product is not enabled
});

Route::post('/cart', function (Request $request) {
    // WIP probs need some sort of csrf checking here
    // WIP and some validation

    $slug = $request->slug;
    $quantity = $request->quantity;

    $items = $request->session()->get('cart');

    collect($items)
        ->where('slug', $slug)
        ->each(function ($item, $quantity) {
            $item['quantity'] = $quantity;
        });

    $items[] = [
        'slug' => $slug,
        'quantity' => $quantity,
    ];

    $request->session()->put('cart', $items);

    return redirect()
        ->back()
        ->with('message', 'Added product to Cart');
});

Route::get('/checkout', function() {
    return (new View)
        ->template('commerce.checkout')
        ->layout('layout')
        ->with([]);

    // Display the list of items in the users cart
    // Allow the user to input their payment information
});

Route::post('/checkout', function(Request $request) {
    dd($request->all());

    // process the payment information to create stripe order & customer
    // create order and customer in addon
    // Send a notification to the customer and the store owner
    // Present user with success message
});
