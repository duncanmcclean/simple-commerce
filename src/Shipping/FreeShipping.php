<?php

namespace DuncanMcClean\SimpleCommerce\Shipping;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use Illuminate\Support\Collection;

class FreeShipping extends ShippingMethod
{
    protected static $title = 'Free Shipping';

    public function options(Cart $cart): Collection
    {
        return collect([
            ShippingOption::make($this)
                ->name(__('Free Shipping'))
                ->price(0),
        ]);
    }
}
