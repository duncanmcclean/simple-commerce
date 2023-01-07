<?php

namespace DoubleThreeDigital\SimpleCommerce\Shipping;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Contracts\ShippingMethod;
use DoubleThreeDigital\SimpleCommerce\Orders\Address;

class FreeShipping extends BaseShippingMethod implements ShippingMethod
{
    public function name(): string
    {
        return __('Free Shipping');
    }

    public function description(): string
    {
        return __("You don't need to pay for shipping, since it's free!");
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
