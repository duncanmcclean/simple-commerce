<?php

namespace DuncanMcClean\SimpleCommerce\Contracts\Shipping;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;

interface ShippingMethod
{
    public function name(): string;

    public function isAvailable(Cart $cart): bool;

    public function cost(Cart $cart): int;
}
