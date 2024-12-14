<?php

namespace DuncanMcClean\SimpleCommerce\Tags;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\ShippingMethod;
use Statamic\Tags\Tags;

class Shipping extends Tags
{
    public function methods()
    {
        $cart = Cart::current();

        return ShippingMethod::all()
            ->flatMap->options($cart)
            ->filter()
            ->values()
            ->all();
    }
}
