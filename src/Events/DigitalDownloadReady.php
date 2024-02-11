<?php

namespace DuncanMcClean\SimpleCommerce\Events;

use DuncanMcClean\SimpleCommerce\Contracts\Order;

class DigitalDownloadReady
{
    public function __construct(public Order $order)
    {
    }
}
