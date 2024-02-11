<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Fixtures\ShippingMethods;

use DuncanMcClean\SimpleCommerce\Contracts\Order as OrderContract;
use DuncanMcClean\SimpleCommerce\Contracts\ShippingMethod;
use DuncanMcClean\SimpleCommerce\Orders\Address;
use DuncanMcClean\SimpleCommerce\Shipping\BaseShippingMethod;

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
