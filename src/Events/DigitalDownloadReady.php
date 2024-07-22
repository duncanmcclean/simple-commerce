<?php

namespace DuncanMcClean\SimpleCommerce\Events;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;

class DigitalDownloadReady
{
    public function __construct(public Order $order) {}
}
