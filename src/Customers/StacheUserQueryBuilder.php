<?php

namespace DoubleThreeDigital\SimpleCommerce\Customers;

use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use Statamic\Stache\Query\UserQueryBuilder;

class StacheUserQueryBuilder extends UserQueryBuilder
{
    public function get($columns = ['*'])
    {
        $get = parent::get($columns);

        return $get->map(fn ($item) => Customer::fromUser($item));
    }
}
