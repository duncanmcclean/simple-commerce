<?php

namespace DuncanMcClean\SimpleCommerce\Customers;

use DuncanMcClean\SimpleCommerce\Facades\Customer;
use Illuminate\Support\Facades\Schema;
use Statamic\Facades\Blink;
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
            return $this->builder->getModel()->getKeyName();
        }

        if (! $this->columnExists($column)) {
            $column = "data->{$column}";
        }

        return $column;
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
