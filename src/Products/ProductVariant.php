<?php

namespace DoubleThreeDigital\SimpleCommerce\Products;

use DoubleThreeDigital\SimpleCommerce\Contracts\Product;
use DoubleThreeDigital\SimpleCommerce\Facades\Product as ProductFacade;
use DoubleThreeDigital\SimpleCommerce\Support\Traits\HasData;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class ProductVariant
{
    use HasData, FluentlyGetsAndSets;

    protected $key;
    protected $product;
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

    public function product($product = null)
    {
        return $this
            ->fluentlyGetOrSet('product')
            ->setter(function ($product) {
                if (! $product instanceof Product) {
                    return ProductFacade::find($product);
                }

                return $product;
            })
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
        if ($this->stock === null) {
            return null;
        }

        return (int) $this->stock;
    }

    public function set(string $key, $value): self
    {
        $this->product()->set(
            'product_variants',
            collect($this->product()->get('product_variants'))
                ->map(function ($itemValue, $itemKey) use ($key, $value) {
                    if ($itemKey === 'options') {
                        foreach ($itemValue as $i => $option) {
                            if ($itemValue[$i]['key'] === $this->key()) {
                                $itemValue[$i][$key] = $value;
                            }
                        }
                    }

                    return $itemValue;
                })
                ->toArray()
        )->save();

        return $this;
    }
}
