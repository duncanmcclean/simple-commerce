<?php

namespace DuncanMcClean\SimpleCommerce\Shipping;

use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Contracts\ShippingMethod;
use DuncanMcClean\SimpleCommerce\Orders\Address;

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
