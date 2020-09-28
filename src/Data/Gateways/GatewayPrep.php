<?php

namespace DoubleThreeDigital\SimpleCommerce\Data\Gateways;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use Statamic\Entries\Entry;

class GatewayPrep
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
        return Cart::find($this->order->id());
    }
}
