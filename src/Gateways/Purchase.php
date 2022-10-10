<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;

class Purchase
{
    public function __construct(protected $request, protected Order $order)
    {
    }

    public function request()
    {
        return $this->request;
    }

    public function order()
    {
        return $this->order;
    }

    public function __call($name, $arguments)
    {
        if ($attribute = $this->order->get($name)) {
            return $attribute;
        }
    }
}
