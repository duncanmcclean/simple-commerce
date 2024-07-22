<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

enum OrderStatus: string
{
    case Cart = 'cart';
    case PendingPayment = 'pending_payment';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function is($orderStatus): bool
    {
        if (! is_string($orderStatus)) {
            $orderStatus = $orderStatus->value;
        }

        return $this->value === $orderStatus;
    }
}
