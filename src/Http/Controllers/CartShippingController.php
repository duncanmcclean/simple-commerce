<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\ShippingMethod;

class CartShippingController
{
    public function __invoke()
    {
        $cart = Cart::current();

        return ShippingMethod::all()
            ->flatMap->options($cart)
            ->filter()
            ->map->toArray()
            ->values()
            ->all();
    }
}