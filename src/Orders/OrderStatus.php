<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

enum OrderStatus: string
{
    case Cart = 'cart';
    case Placed = 'placed';
    case Shipped = 'shipped'; // TODO: replace Shipped with Dispatched
    case Cancelled = 'cancelled';

    public function is($orderStatus): bool
    {
        if (! is_string($orderStatus)) {
            $orderStatus = $orderStatus->value;
        }

        return $this->value === $orderStatus;
    }
}
