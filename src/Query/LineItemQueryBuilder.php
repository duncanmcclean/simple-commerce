<?php

namespace DuncanMcClean\SimpleCommerce\Query;

use Statamic\Query\Builder;

class LineItemQueryBuilder extends Builder
{
    public function inRandomOrder()
    {
        // TODO: Implement inRandomOrder() method.
    }

    protected function getCountForPagination()
    {
        // TODO: Implement getCountForPagination() method.
    }

    public function count()
    {
        // TODO: Implement count() method.
    }

    public function get($columns = ['*'])
    {
        // TODO: Implement get() method.
    }

    public function pluck($column, $key = null)
    {
        // TODO: Implement pluck() method.
    }

    public function getWheres()
    {
        return $this->wheres;
    }
}