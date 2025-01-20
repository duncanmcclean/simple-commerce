<?php

namespace DuncanMcClean\SimpleCommerce\Payments\Gateways;

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
        return app('statamic.extensions')[PaymentGateway::class]
            ->filter(fn ($class) => config()->has('statamic.simple-commerce.payments.gateways.'.$class::handle()));
    }
}
