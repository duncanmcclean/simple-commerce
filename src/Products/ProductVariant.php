<?php

namespace DuncanMcClean\SimpleCommerce\Products;

use DuncanMcClean\SimpleCommerce\Contracts\Product;
use DuncanMcClean\SimpleCommerce\Data\HasData;
use DuncanMcClean\SimpleCommerce\Facades\Product as ProductFacade;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class ProductVariant
{
    use FluentlyGetsAndSets, HasData;

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
            ->setter(function ($value) {
                if ($value === null) {
                    return null;
                }

                return (int) $value;
            })
            ->args(func_get_args());
    }

    public function save(): self
    {
        $this->product->productVariants(
            collect($this->product->productVariants())
                ->map(function ($itemValue, $itemKey) {
                    if ($itemKey === 'options') {
                        foreach ($itemValue as $i => $option) {
                            if ($itemValue[$i]['key'] === $this->key()) {
                                $variantData = [
                                    'key' => $this->key(),
                                    'variant' => $this->name(),
                                    'price' => $this->price(),
                                    'stock' => $this->stock(),
                                ];

                                $variantData = array_merge(
                                    $variantData,
                                    $this->data()->except(['key', 'name', 'price', 'stock'])->toArray()
                                );

                                $itemValue[$i] = $variantData;
                            }
                        }
                    }

                    return $itemValue;
                })
                ->toArray()
        );

        $this->product->save();

        return $this;
    }
}
