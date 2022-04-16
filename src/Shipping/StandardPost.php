<?php

namespace DoubleThreeDigital\SimpleCommerce\Shipping;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Contracts\ShippingMethod;
use DoubleThreeDigital\SimpleCommerce\Orders\Address;

class StandardPost extends BaseShippingMethod implements ShippingMethod
{
    public function name(): string
    {
        return __('simple-commerce::messages.shipping_methods.standard_post.name');
    }

    public function description(): string
    {
        return __('simple-commerce::messages.shipping_methods.standard_post.description');
    }

    public function calculateCost(Order $order): int
    {
        return 100;
    }

    public function checkAvailability(Order $order, Address $address): bool
    {
        return true;
    }
}
