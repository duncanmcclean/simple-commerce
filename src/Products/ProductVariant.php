<?php

namespace DoubleThreeDigital\SimpleCommerce\Products;

use DoubleThreeDigital\SimpleCommerce\Support\Traits\HasData;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class ProductVariant
{
    use HasData, FluentlyGetsAndSets;

    protected $key;
    protected $name;
    protected $price;
    protected $data;

    public function key($key = null)
    {
        return $this
            ->fluentlyGetOrSet('key')
            ->args(func_get_args());
    }

    public function name($name = null)
    {
        return $this
            ->fluentlyGetOrSet('name')
            ->args(func_get_args());
    }

    public function price($price = null)
    {
        return $this
            ->fluentlyGetOrSet('price')
            ->args(func_get_args());
    }
}
