<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use Carbon\Carbon;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use Statamic\Stache\Query\EntryQueryBuilder as QueryEntryQueryBuilder;

class EntryQueryBuilder extends QueryEntryQueryBuilder
{
    public function get($columns = ['*'])
    {
        $get = parent::get($columns);

        return $get->map(fn ($entry) => Order::fromEntry($entry));
    }

    public function whereOrderStatus(OrderStatus $orderStatus)
    {
        return $this->where('order_status', $orderStatus->value);
    }

    public function wherePaymentStatus(PaymentStatus $paymentStatus)
    {
        return $this->where('payment_status', $paymentStatus->value);
    }

    public function whereStatusLogDate(OrderStatus|PaymentStatus $status, Carbon $date)
    {
        return $this->whereDate("status_log->{$status->value}", $date->format('d-m-Y'));
    }
}
