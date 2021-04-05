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

    // TODO: can we remove this method?
    public function cart()
    {
        return OrderFacade::find($this->order->id());
    }

    public function __call($name, $arguments)
    {
        if ($attribute = $this->order->get($name)) {
            return $attribute;
        }
    }
}
