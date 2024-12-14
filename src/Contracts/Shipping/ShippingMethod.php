<?php

namespace DuncanMcClean\SimpleCommerce\Contracts\Shipping;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use Illuminate\Support\Collection;

interface ShippingMethod
{
    public function name(): string;

    public function options(Cart $cart): Collection;
}
