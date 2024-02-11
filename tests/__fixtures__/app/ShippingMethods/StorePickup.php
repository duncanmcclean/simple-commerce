<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Fixtures\ShippingMethods;

use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Contracts\ShippingMethod;
use DuncanMcClean\SimpleCommerce\Orders\Address;
use DuncanMcClean\SimpleCommerce\Shipping\BaseShippingMethod;

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
