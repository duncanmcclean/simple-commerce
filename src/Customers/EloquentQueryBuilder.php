<?php

namespace DoubleThreeDigital\SimpleCommerce\Customers;

use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use Statamic\Query\EloquentQueryBuilder as QueryEloquentQueryBuilder;

class EloquentQueryBuilder extends QueryEloquentQueryBuilder
{
    protected function transform($items, $columns = ['*'])
    {
        return $items->map(fn ($item) => Customer::fromModel($item));
    }

    protected function column($column)
    {
        if ($column === 'id') {
            return 'id'; // TODO: make this based on order model keyName
        }

        return $column;
    }
}
