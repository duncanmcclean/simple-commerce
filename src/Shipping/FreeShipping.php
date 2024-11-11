<?php

namespace DuncanMcClean\SimpleCommerce\Shipping;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;

class FreeShipping extends ShippingMethod
{
    public function name(): string
    {
        return __('Free Shipping');
    }

    public function isAvailable(Cart $cart): bool
    {
        return true;
    }

    public function cost(Cart $cart): int
    {
        return 0;
    }
}
