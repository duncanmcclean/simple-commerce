<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

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
            return 'id'; // TODO: make this based on order model keyName
        }

        if ($column === 'customer') {
            return 'customer_id';
        }

        return $column;
    }
}
