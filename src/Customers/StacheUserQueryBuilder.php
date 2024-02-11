<?php

namespace DuncanMcClean\SimpleCommerce\Customers;

use DuncanMcClean\SimpleCommerce\Facades\Customer;
use Statamic\Stache\Query\UserQueryBuilder;

class StacheUserQueryBuilder extends UserQueryBuilder
{
    public function get($columns = ['*'])
    {
        $get = parent::get($columns);

        return $get->map(fn ($item) => Customer::fromUser($item));
    }
}
