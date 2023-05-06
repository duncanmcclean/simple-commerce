<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Orders\Helpers;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\ShippingMethod;
use DoubleThreeDigital\SimpleCommerce\Orders\Address;
use DoubleThreeDigital\SimpleCommerce\Shipping\BaseShippingMethod;

class Postage extends BaseShippingMethod implements ShippingMethod
{
    public function name(): string
    {
        return __('simple-commerce::shipping.standard_post.name');
    }

    public function description(): string
    {
        return __('simple-commerce::shipping.standard_post.description');
    }

    public function calculateCost(OrderContract $order): int
    {
        return 250;
    }

    public function checkAvailability(OrderContract $order, Address $address): bool
    {
        return true;
    }
}
