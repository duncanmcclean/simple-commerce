<?php

namespace DoubleThreeDigital\SimpleCommerce\Data\Gateways;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use Statamic\Entries\Entry;

class GatewayPurchase
{
    protected $request;
    protected $order;

    public function __construct($request, Entry $order)
    {
        $this->request = $request;
        $this->order = $order;
    }

    public function request()
    {
        return $this->request;
    }

    public function order()
    {
        return $this->order;
    }

    public function cart()
    {
        return Order::find($this->order->id());
    }
}
