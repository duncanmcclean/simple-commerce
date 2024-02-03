<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;

class DigitalDownloadReady
{
    public function __construct(public Order $order)
    {
    }
}
