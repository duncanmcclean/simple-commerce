<?php

namespace DuncanMcClean\SimpleCommerce\Products;

use DuncanMcClean\SimpleCommerce\Contracts\Products\Product as ProductContract;
use DuncanMcClean\SimpleCommerce\Contracts\Purchasable;
use DuncanMcClean\SimpleCommerce\Contracts\Taxes\TaxClass;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use Illuminate\Support\Traits\Conditionable;
use Statamic\Data\ContainsData;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class ProductVariant implements Purchasable
{
    use FluentlyGetsAndSets, ContainsData, Conditionable;

    public $key;
    public $product;
    public $name;
    public $price;
    public $stock;

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
            ->getter(function ($product) {
                if (! $product) {
                    return null;
                }

                return Product::find($product);
            })
            ->setter(function ($product) {
                if ($product instanceof ProductContract) {
                    return $product->id();
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
            ->getter(function ($price) {
                if (str_contains($price, '.')) {
                    $price = number_format($price, 2, '.', '');
                    $price = (int) str_replace('.', '', (string) $price);
                }

                return (int) $price ?? 0;
            })
            ->args(func_get_args());
    }

    public function stock($stock = null)
    {
        return $this
            ->fluentlyGetOrSet('stock')
            ->setter(function ($value) {
                if (is_null($value)) {
                    return null;
                }

                return (int) $value;
            })
            ->args(func_get_args());
    }

    public function purchasablePrice(): int
    {
        return $this->price();
    }

    public function purchasableTaxClass(): ?TaxClass
    {
        return $this->product()->value('tax_class');
    }
}
