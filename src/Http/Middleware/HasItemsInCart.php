<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Middleware;

use Closure;
use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use Illuminate\Support\Facades\Session;

class HasItemsInCart
{
    public function handle($request, Closure $next)
    {
        if (!Session::has(config('simple-commerce.cart_session_key'))) {
            // The customer does not have a cart key in their session
            abort(401);
        } else {
            // The customer has a cart key in their session but has no items

            $itemsTotal = Cart::find(Session::get(config('simple-commerce.cart_session_key')))->get('items_count');

            if ($itemsTotal === 0) {
                abort(401);
            }
        }

        return $next($request);
    }
}
