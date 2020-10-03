<?php

namespace DoubleThreeDigital\SimpleCommerce\Repositories;

use DoubleThreeDigital\SimpleCommerce\Contracts\ShippingRepository as ContractsShippingRepository;
use DoubleThreeDigital\SimpleCommerce\Data\Address;
use DoubleThreeDigital\SimpleCommerce\Exceptions\ShippingMethodDoesNotExist;
use Statamic\Entries\Entry;

class ShippingRepository implements ContractsShippingRepository
{
    protected $className;

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

    public function calculateCost(Entry $order): int
    {
        return $this->resolve()->calculateCost($order);
    }

    public function checkAvailability(Address $address): bool
    {
        return $this->resolve()->checkAvailability($address);
    }

    protected function resolve()
    {
        if (! resolve($this->className)) {
            throw new ShippingMethodDoesNotExist(__('simple-commerce::shipping.shipping_method_does_not_exist', ['shippingMethod' => $this->className]));
        }

        return resolve($this->className);
    }
}
