<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use Statamic\Entries\Entry;

interface ShippingMethod
{
    public function name(): string;
    public function description(): string;
    public function calculateCost(Entry $order): int;
    public function checkAvilability(array $address): bool;
}