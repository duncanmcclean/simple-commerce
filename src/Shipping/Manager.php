<?php

namespace DoubleThreeDigital\SimpleCommerce\Shipping;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Contracts\ShippingManager as Contract;
use DoubleThreeDigital\SimpleCommerce\Exceptions\ShippingMethodDoesNotExist;
use DoubleThreeDigital\SimpleCommerce\Orders\Address;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Facades\Site;

class Manager implements Contract
{
    protected $siteHandle;

    protected $handle;

    public function site($siteHandle): self
    {
        $this->siteHandle = $siteHandle;

        return $this;
    }

    public function use($handle): self
    {
        $this->handle = $handle;

        return $this;
    }

    public function name(): string
    {
        return $this->resolve()->name();
    }

    public function description(): string
    {
        return $this->resolve()->description();
    }

    public function calculateCost(Order $order): int
    {
        return $this->resolve()->calculateCost($order);
    }

    public function checkAvailability(Order $order, Address $address): bool
    {
        return $this->resolve()->checkAvailability($order, $address);
    }

    public function resolve()
    {
        $siteHandle = $this->siteHandle ?? Site::current()->handle();

        $shippingMethod = SimpleCommerce::shippingMethods($siteHandle)
            ->filter(function ($shippingMethod) {
                return $this->handle === $shippingMethod['class']::handle() || $shippingMethod['class'] === $this->handle;
            })
            ->first();

        if (! $shippingMethod) {
            throw new ShippingMethodDoesNotExist("Shipping method [{$this->handle}] does not exist.");
        }

        return resolve($shippingMethod['class'], [
            'config' => $shippingMethod['config'] ?? [],
        ]);
    }
}
