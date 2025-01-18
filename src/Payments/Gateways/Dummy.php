<?php

namespace DuncanMcClean\SimpleCommerce\Payments\Gateways;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Dummy extends PaymentGateway
{
    public function setup(Cart $cart): array
    {
        // TODO: Implement setup() method.

        return [];
    }

    public function process(Order $order): void
    {
        // TODO: Implement process() method.
    }

    public function capture(Order $order): void
    {
        // TODO: Implement capture() method.
    }

    public function cancel(Cart $cart): void
    {
        // TODO: Implement cancel() method.
    }

    public function webhook(Request $request): Response
    {
        // TODO: Implement webhook() method.

        return response();
    }

    public function refund(Order $order, int $amount): void
    {
        // TODO: Implement refund() method.
    }
}
