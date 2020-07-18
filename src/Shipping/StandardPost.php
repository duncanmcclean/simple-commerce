<?php

namespace DoubleThreeDigital\SimpleCommerce\Shipping;

use DoubleThreeDigital\SimpleCommerce\Contracts\ShippingMethod;
use Statamic\Entries\Entry;

class StandardPost implements ShippingMethod
{
    public function name(): string
    {
        return __('simple-commerce::shipping.standard_post.name');
    }

    public function description(): string
    {
        return __('simple-commerce::shipping.standard_post.description');
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