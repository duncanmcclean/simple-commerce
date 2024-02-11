<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Fixtures\ShippingMethods;

use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Contracts\ShippingMethod;
use DuncanMcClean\SimpleCommerce\Orders\Address;
use DuncanMcClean\SimpleCommerce\Shipping\BaseShippingMethod;

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
