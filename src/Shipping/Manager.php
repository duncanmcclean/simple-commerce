<?php

namespace DuncanMcClean\SimpleCommerce\Shipping;

use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Contracts\ShippingManager as Contract;
use DuncanMcClean\SimpleCommerce\Exceptions\ShippingMethodDoesNotExist;
use DuncanMcClean\SimpleCommerce\Orders\Address;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
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

        $shippingMethod = SimpleCommerce::shippingMethods($siteHandle)->firstWhere('handle', $this->handle);

        if (! $shippingMethod) {
            throw new ShippingMethodDoesNotExist("Shipping method [{$this->handle}] does not exist.");
        }

        return resolve($shippingMethod['class'], [
            'config' => $shippingMethod['config'] ?? [],
        ]);
    }
}
