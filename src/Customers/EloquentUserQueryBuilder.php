<?php

namespace DuncanMcClean\SimpleCommerce\Customers;

use DuncanMcClean\SimpleCommerce\Facades\Customer;
use Statamic\Auth\Eloquent\UserQueryBuilder;
use Statamic\Facades\User;

class EloquentUserQueryBuilder extends UserQueryBuilder
{
    protected function transform($items, $columns = ['*'])
    {
        return $items
            ->map(fn ($item) => User::make()->model($item))
            ->map(fn ($item) => Customer::fromUser($item));
    }
}
