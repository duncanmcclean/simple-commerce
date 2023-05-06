<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags\Helpers;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Contracts\ShippingMethod;
use DoubleThreeDigital\SimpleCommerce\Orders\Address;
use DoubleThreeDigital\SimpleCommerce\Shipping\BaseShippingMethod;

class StorePickup extends BaseShippingMethod implements ShippingMethod
{
    public function name(): string
    {
        return 'Store Pickup - '.$this->config()->get('location');
    }

    public function description(): string
    {
        return 'Pick up your parcel from the store.';
    }

    public function calculateCost(Order $order): int
    {
        return 0;
    }

    public function checkAvailability(Order $order, Address $address): bool
    {
        return true;
    }
}
