<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Eloquent;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\Query\EloquentQueryBuilder;

class OrderQueryBuilder extends EloquentQueryBuilder
{
    protected $columns = [
        'id', 'order_number', 'date', 'site', 'cart', 'status', 'customer', 'coupon', 'grand_total',
        'sub_total', 'discount_total', 'tax_total', 'shipping_total', 'line_items', 'data',
    ];

    public function orderBy($column, $direction = 'asc')
    {
        $column = $this->column($column);

        return parent::orderBy($column, $direction);
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $column = $this->column($column);

        if ($column === 'customer') {
            return $this->queryByCustomer($operator, $value, $boolean);
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    public function whereHasLineItem(?Closure $callback = null): self
    {
        $this->builder->whereHas('lineItems', $callback);

        return $this;
    }

    /**
     * The "customer" column contains both user IDs and JSON objects for guest customers. In order
     * to query guest customers, we need to handle the query differently depending on the value.
     *
     * We don't need to do this for flat file orders because the Stache uses the getQueryableValue() method.
     */
    private function queryByCustomer($operator = null, $value = null, $boolean = 'and'): self
    {
        if (Str::startsWith($value ?? $operator, 'guest::')) {
            $email = Str::after($value ?? $operator, 'guest::');

            $this->builder->whereRaw("customer LIKE '{%' AND json_extract(customer, '$.email') = ?", [$email]);

            return $this;
        }

        return parent::where('customer', $operator, $value, $boolean);
    }

    public function pluck($column, $key = null)
    {
        $column = $this->column($column);

        return $this->builder->pluck($column, $key);
    }

    protected function transform($items, $columns = ['*'])
    {
        return Collection::make($items)->map(function ($model) {
            return Order::fromModel($model);
        });
    }

    protected function column($column): string
    {
        if (! is_string($column)) {
            return $column;
        }

        if (! in_array($column, $this->columns)) {
            if (! Str::startsWith($column, 'data->')) {
                $column = 'data->'.$column;
            }
        }

        return $column;
    }
}
