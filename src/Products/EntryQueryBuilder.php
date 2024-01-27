<?php

namespace DoubleThreeDigital\SimpleCommerce\Products;

use Statamic\Stache\Query\EntryQueryBuilder as QueryEntryQueryBuilder;

class EntryQueryBuilder extends QueryEntryQueryBuilder
{
    public function get($columns = ['*'])
    {
        $get = parent::get($columns);

        return $get->map(fn ($entry) => Product::fromEntry($entry));
    }
}
