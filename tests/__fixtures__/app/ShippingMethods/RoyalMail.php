<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Fixtures\ShippingMethods;

use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Contracts\ShippingMethod;
use DuncanMcClean\SimpleCommerce\Orders\Address;
use DuncanMcClean\SimpleCommerce\Shipping\BaseShippingMethod;

class RoyalMail extends BaseShippingMethod implements ShippingMethod
{
    public function name(): string
    {
        return 'Royal Mail';
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
        return true;
    }
}
