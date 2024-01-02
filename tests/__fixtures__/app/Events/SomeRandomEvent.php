<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Fixtures\Events;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;

class SomeRandomEvent
{
    public function __construct(public OrderContract $order)
    {
    }
}
