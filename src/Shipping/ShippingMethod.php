<?php

namespace DuncanMcClean\SimpleCommerce\Shipping;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use Illuminate\Support\Collection;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;

abstract class ShippingMethod
{
    use HasHandle, HasTitle, RegistersItself;

    public function logo(): ?string
    {
        return null;
    }

    abstract public function options(Cart $cart): Collection;
}
