<?php

namespace DuncanMcClean\SimpleCommerce\Events;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;

class OrderCreated
{
    public function __construct(public Order $order) {}
}
