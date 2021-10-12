<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use DoubleThreeDigital\SimpleCommerce\Orders\Address;

interface ShippingMethod
{
    public function name(): string;

    public function description(): string;

    public function calculateCost(Order $order): int;

    public function checkAvailability(Order $order, Address $address): bool;
}
