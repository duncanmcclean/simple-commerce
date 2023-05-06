<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags\Helpers;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Contracts\ShippingMethod;
use DoubleThreeDigital\SimpleCommerce\Orders\Address;
use DoubleThreeDigital\SimpleCommerce\Shipping\BaseShippingMethod;

class DPD extends BaseShippingMethod implements ShippingMethod
{
    public function name(): string
    {
        return 'DPD';
    }

    public function description(): string
    {
        return 'Description of your shipping method';
    }

    public function calculateCost(Order $order): int
    {
        return 0;
    }

    public function checkAvailability(Order $order, Address $address): bool
    {
        return false;
    }
}
