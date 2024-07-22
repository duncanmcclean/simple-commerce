<?php

namespace DuncanMcClean\SimpleCommerce\Contracts\Shipping;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;
use DuncanMcClean\SimpleCommerce\Orders\Address;

interface ShippingMethod
{
    public function name(): string;

    public function description(): string;

    public function calculateCost(Order $order): int;

    public function checkAvailability(Order $order, Address $address): bool;
}
