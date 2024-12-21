<?php

namespace DuncanMcClean\SimpleCommerce\Shipping;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Contracts\Shipping\ShippingMethod as Contract;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\Extend\HasHandle;
use Statamic\Extend\RegistersItself;

abstract class ShippingMethod implements Contract
{
    use HasHandle, RegistersItself;

    public function name(): string
    {
        return Str::title(class_basename($this));
    }

    public function logo(): ?string
    {
        return null;
    }

    abstract public function options(Cart $cart): Collection;
}
