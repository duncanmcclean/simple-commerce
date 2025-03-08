<?php

namespace DuncanMcClean\SimpleCommerce\Query;

trait QueriesLineItems
{
    public function whereHasLineItem($column, $operator = null, $value = null, $boolean = 'and'): self
    {
        if (! $value) {
            $value = $operator;
            $operator = '=';
        }

        if ($column instanceof \Closure) {
            $query = app(LineItemQueryBuilder::class);
            $column($query);

            foreach ($query->getWheres() as $where) {
                $this->whereHasLineItem($where['column'], $where['operator'], $where['value'], $where['boolean']);
            }

            return $this;
        }

        $this->where("line_items->{$column}", $operator, $value, $boolean);

        return $this;
    }
}