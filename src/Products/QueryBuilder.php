<?php

namespace DuncanMcClean\SimpleCommerce\Products;

use DuncanMcClean\SimpleCommerce\Facades\Product;
use Statamic\Stache\Query\EntryQueryBuilder;

class QueryBuilder extends EntryQueryBuilder
{
    public function get($columns = ['*'])
    {
        $get = parent::get($columns);

        return $get->map(fn ($entry) => Product::fromEntry($entry));
    }
}
