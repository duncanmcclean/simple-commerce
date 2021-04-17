<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use DoubleThreeDigital\SimpleCommerce\Orders\Address;

interface ShippingManager
{
    public function name(): string;

    public function description(): string;

    public function calculateCost(Order $order): int;

    public function checkAvailability(Address $address): bool;
}
