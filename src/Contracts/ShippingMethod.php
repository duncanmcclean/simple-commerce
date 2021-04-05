<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use DoubleThreeDigital\SimpleCommerce\Orders\Address;
use Statamic\Entries\Entry;

interface ShippingMethod
{
    public function name(): string;

    public function description(): string;

    // TODO: pass in Order, instead of Entry
    public function calculateCost(Entry $order): int;

    public function checkAvailability(Address $address): bool;
}
