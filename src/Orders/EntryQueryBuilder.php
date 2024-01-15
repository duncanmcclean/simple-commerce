<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use Statamic\Stache\Query\EntryQueryBuilder as QueryEntryQueryBuilder;

class EntryQueryBuilder extends QueryEntryQueryBuilder
{
    public function get($columns = ['*'])
    {
        $get = parent::get($columns);

        return $get->map(fn ($entry) => Order::fromEntry($entry));
    }
}
