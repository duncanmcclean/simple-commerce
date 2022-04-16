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
    protected $className;

    public function site($siteHandle): self
    {
        $this->className = $siteHandle;

        return $this;
    }

    public function use($className): self
    {
        $this->className = $className;

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

    protected function resolve()
    {
        if (! resolve($this->className)) {
            throw new ShippingMethodDoesNotExist("Shipping method [{$this->className}] does not exist.");
        }

        $siteHandle = $this->siteHandle ?? Site::current()->handle();

        $shippingMethod = SimpleCommerce::shippingMethods($siteHandle)
            ->where('class', $this->className)
            ->first();

        return resolve($this->className, [
            'config' => $shippingMethod['config'],
        ]);
    }

    public static function bindings(): array
    {
        return [];
    }
}
