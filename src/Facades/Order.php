<?php

namespace Damcclean\Commerce\Facades;

use Damcclean\Commerce\Contracts\OrderRepository;
use Illuminate\Support\Facades\Facade;

class Order extends Facade
{
    protected static function getFacadeAccessor()
    {
        return OrderRepository::class;
    }
}
