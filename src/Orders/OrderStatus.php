<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

enum OrderStatus: string
{
    case PaymentPending = 'payment_pending';
    case PaymentReceived = 'payment_received';
    case Shipped = 'shipped';
    case Returned = 'returned';
    case Cancelled = 'cancelled';

    public static function label($status): string
    {
        return match ($status) {
            self::PaymentPending => __('Payment Pending'),
            self::PaymentReceived => __('Payment Received'),
            self::Shipped => __('Shipped'),
            self::Returned => __('Returned'),
            self::Cancelled => __('Cancelled'),
        };
    }
}
