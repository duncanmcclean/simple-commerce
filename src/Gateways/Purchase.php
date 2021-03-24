<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Order as OrderFacade;

class Purchase
{
    protected $request;
    protected $order;

    public function __construct($request, Order $order)
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
        return OrderFacade::find($this->order->id());
    }
}
