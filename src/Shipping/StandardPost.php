<?php

namespace DoubleThreeDigital\SimpleCommerce\Shipping;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Contracts\ShippingMethod;
use DoubleThreeDigital\SimpleCommerce\Orders\Address;

class StandardPost extends BaseShippingMethod implements ShippingMethod
{
    public function name(): string
    {
        return __('Standard Post');
    }

    public function description(): string
    {
        return __('Posted through the national post service. Usually delivered within 1-2 working days.');
    }

    public function calculateCost(Order $order): int
    {
        return 100;
    }

    public function checkAvailability(Order $order, Address $address): bool
    {
        return true;
    }
}
