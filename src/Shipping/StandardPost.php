<?php

namespace DoubleThreeDigital\SimpleCommerce\Shipping;

use DoubleThreeDigital\SimpleCommerce\Contracts\ShippingMethod;
use Statamic\Entries\Entry;

class StandardPost implements ShippingMethod
{
    public function name(): string
    {
        return 'Standard Post';
    }

    public function description(): string
    {
        return 'Posted through the national post service. Usually delivered within 1-2 working days.';
    }

    public function calculateCost(Entry $order): int
    {
        return 120;
    }

    public function checkAvailability(array $address): bool
    {
        return true;
    }
}