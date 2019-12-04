<?php

namespace Damcclean\Commerce\Facades;

use Damcclean\Commerce\Contracts\ProductRepository;
use Illuminate\Support\Facades\Facade;

class Product extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ProductRepository::class;
    }
}
