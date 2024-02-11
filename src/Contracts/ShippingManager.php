<?php

namespace DuncanMcClean\SimpleCommerce\Contracts;

use DuncanMcClean\SimpleCommerce\Orders\Address;

interface ShippingManager
{
    public function name(): string;

    public function description(): string;

    public function calculateCost(Order $order): int;

    public function checkAvailability(Order $order, Address $address): bool;

    public function resolve();
}
