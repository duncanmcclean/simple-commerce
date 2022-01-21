<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use Statamic\Tags\Tags;

class OrderTags extends Tags
{
    public function receiptUrl()
    {
        $order = Order::find($this->params->get('order'));

        return $order->receiptUrl();
    }
}
