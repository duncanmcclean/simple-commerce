<?php

namespace DuncanMcClean\SimpleCommerce\Stache\Query;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\QueryBuilder;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use Statamic\Stache\Query\Builder;

class OrderQueryBuilder extends Builder implements QueryBuilder
{
    public function whereStatus(string|OrderStatus $status): self
    {
        if ($status instanceof OrderStatus) {
            $status = $status->value;
        }

        $this->where('status', $status);

        return $this;
    }

    public function whereNotStatus(string|OrderStatus $status): self
    {
        if ($status instanceof OrderStatus) {
            $status = $status->value;
        }

        $this->where('status', '!=', $status);

        return $this;
    }

    protected function getFilteredKeys()
    {
        if (! empty($this->wheres)) {
            return $this->getKeysWithWheres($this->wheres);
        }

        return collect($this->store->paths()->keys());
    }

    protected function getKeysWithWheres($wheres)
    {
        return collect($wheres)->reduce(function ($ids, $where) {
            $keys = $where['type'] == 'Nested'
                ? $this->getKeysWithWheres($where['query']->wheres)
                : $this->getKeysWithWhere($where);

            return $this->intersectKeysFromWhereClause($ids, $keys, $where);
        });
    }

    protected function getKeysWithWhere($where)
    {
        $items = app('stache')
            ->store('orders')
            ->index($where['column'])->items();

        $method = 'filterWhere'.$where['type'];

        return $this->{$method}($items, $where)->keys();
    }

    protected function getOrderKeyValuesByIndex()
    {
        return collect($this->orderBys)->mapWithKeys(function ($orderBy) {
            $items = $this->store->index($orderBy->sort)->items()->all();

            return [$orderBy->sort => $items];
        });
    }
}
