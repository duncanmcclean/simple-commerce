<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Fixtures\ShippingMethods;

use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Contracts\ShippingMethod;
use DuncanMcClean\SimpleCommerce\Orders\Address;
use DuncanMcClean\SimpleCommerce\Shipping\BaseShippingMethod;

class DummyShippingMethod extends BaseShippingMethod implements ShippingMethod
{
    public function name(): string
    {
        return 'Dummy Shipping Method';
    }

    public function description(): string
    {
        return 'Dummy Shipping Method Description';
    }

    public function calculateCost(Order $order): int
    {
        return 500;
    }

    public function checkAvailability(Order $order, Address $address): bool
    {
        return true;
    }
}
