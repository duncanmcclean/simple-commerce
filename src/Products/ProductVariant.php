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
    protected $stock;
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

    public function stock($stock = null)
    {
        return $this
            ->fluentlyGetOrSet('stock')
            ->args(func_get_args());
    }

    /**
     * Use this to get the product's stock count.
     *
     * @return int
     */
    public function stockCount()
    {
        if (! $this->stock) {
            return null;
        }

        return (int) $this->stock;
    }
}
