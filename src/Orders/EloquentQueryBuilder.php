<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use Carbon\Carbon;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use Statamic\Query\EloquentQueryBuilder as QueryEloquentQueryBuilder;

class EloquentQueryBuilder extends QueryEloquentQueryBuilder
{
    protected function transform($items, $columns = ['*'])
    {
        return $items->map(fn ($item) => Order::fromModel($item));
    }

    protected function column($column)
    {
        if ($column === 'id') {
            return $this->builder->getModel()->getKeyName();
        }

        if ($column === 'customer') {
            return 'customer_id';
        }

        return $column;
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
        return $this->whereHas('statusLog', function ($query) use ($status, $date) {
            return $query
                ->where('status', $status->value)
                ->whereDate('timestamp', $date->format('Y-m-d'));
        });
    }
}
