<?php

namespace DuncanMcClean\SimpleCommerce\Events;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;

class OrderRefunded
{
    public function __construct(public Order $order, public int $amount) {}
}
