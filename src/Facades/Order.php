<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\Contracts\OrderRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @see \DoubleThreeDigital\SimpleCommerce\Contracts\OrderRepository
 */
class Order extends Facade
{
    protected static function getFacadeAccessor()
    {
        return OrderRepository::class;
    }
}
