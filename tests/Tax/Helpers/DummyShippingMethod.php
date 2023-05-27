<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tax\Helpers;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Contracts\ShippingMethod;
use DoubleThreeDigital\SimpleCommerce\Orders\Address;

class DummyShippingMethod implements ShippingMethod
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
