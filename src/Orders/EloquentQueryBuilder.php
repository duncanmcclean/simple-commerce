<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use Carbon\Carbon;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Statamic\Facades\Blink;
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

        if (! $this->columnExists($column)) {
            $column = "data->{$column}";
        }

        return $column;
    }

    public function orderBy($column, $direction = 'asc')
    {
        if (Str::startsWith($column, 'status_log->')) {
            $status = Str::after($column, 'status_log->');

            $this->builder
                ->joinSub(function ($query) use ($status) {
                    $query
                        ->select('order_id', 'timestamp', 'status')
                        ->from('status_log')
                        ->where('status', $status);
                }, 'latest_status_log', function ($join) {
                    $join->on('orders.id', '=', 'latest_status_log.order_id');
                })
                ->orderBy('latest_status_log.timestamp', $direction);

            return $this;
        }

        return parent::orderBy($this->column($column), $direction);
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

    protected function columnExists(string $column): bool
    {
        $databaseColumns = Blink::once("DatabaseColumns_{$this->builder->getModel()->getTable()}", function () {
            return collect(Schema::getColumns($this->builder->getModel()->getTable()))
                ->map(fn (array $column) => $column['name'])
                ->values();
        });

        return $databaseColumns->contains($column);
    }
}
