<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Order as OrderFacade;

class Prepare
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

    public function cart()
    {
        return OrderFacade::find($this->order->id());
    }
}
