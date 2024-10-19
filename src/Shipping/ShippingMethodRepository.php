<?php

namespace DuncanMcClean\SimpleCommerce\Shipping;

use DuncanMcClean\SimpleCommerce\Exceptions\ShippingMethodDoesNotExist;
use Illuminate\Support\Collection;

class ShippingMethodRepository
{
    public function find(string $handle)
    {
        if (! $this->classes()->has($handle)) {
            throw new ShippingMethodDoesNotExist($handle);
        }

        return app($this->classes()->get($handle));
    }

    public function classes(): Collection
    {
        return app('statamic.extensions')[ShippingMethod::class];
    }
}
