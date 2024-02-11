<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Refunded = 'refunded';

    public function is($paymentStatus): bool
    {
        if (! is_string($paymentStatus)) {
            $paymentStatus = $paymentStatus->value;
        }

        return $this->value === $paymentStatus;
    }
}
