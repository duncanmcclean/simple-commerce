<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Fixtures\Events;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order as OrderContract;

class SomeRandomEvent
{
    public function __construct(public OrderContract $order)
    {
    }
}
