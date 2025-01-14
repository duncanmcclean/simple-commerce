<?php

namespace DuncanMcClean\SimpleCommerce\Payments\Gateways;

use DuncanMcClean\SimpleCommerce\Exceptions\PaymentGatewayDoesNotExist;
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
            throw new PaymentGatewayDoesNotExist($handle);
        }

        return app($this->classes()->get($handle));
    }

    public function classes(): Collection
    {
        return app('statamic.extensions')[PaymentGateway::class]
            ->filter(fn ($class) => config()->has('statamic.simple-commerce.payments.gateways.'.$class::handle()));
    }
}
