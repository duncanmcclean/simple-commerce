<?php

namespace DuncanMcClean\SimpleCommerce\Events;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;

class OrderStatusUpdated
{
    public function __construct(public Order $order, public OrderStatus $oldStatus, public OrderStatus $newStatus)
    {
    }
}