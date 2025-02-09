<?php

namespace DuncanMcClean\SimpleCommerce\Shipping;

use Illuminate\Support\Collection;

class Manager
{
    public function all()
    {
        return $this->classes()->map(fn ($class) => app($class));
    }

    public function find(string $handle)
    {
        if (! $this->classes()->has($handle)) {
            return;
        }

        return app($this->classes()->get($handle));
    }

    public function classes(): Collection
    {
        return app('statamic.extensions')[ShippingMethod::class];
    }
}
