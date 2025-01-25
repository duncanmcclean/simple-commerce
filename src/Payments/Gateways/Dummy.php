<?php

namespace DuncanMcClean\SimpleCommerce\Payments\Gateways;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Dummy extends PaymentGateway
{
    public function setup(Cart $cart): array
    {
        return [];
    }

    public function process(Order $order): void
    {
        $order
            ->set('payment_gateway', static::handle())
            ->status(OrderStatus::PaymentReceived)
            ->save();
    }

    public function capture(Order $order): void
    {
        //
    }

    public function cancel(Cart $cart): void
    {
        //
    }

    public function webhook(Request $request): Response
    {
        return response();
    }

    public function refund(Order $order, int $amount): void
    {
        //
    }
}
