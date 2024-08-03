<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use DuncanMcClean\SimpleCommerce\Contracts\Products\Product;
use DuncanMcClean\SimpleCommerce\Facades\Product as ProductFacade;
use Statamic\Data\ContainsData;
use Statamic\Support\Arr;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class LineItem
{
    use FluentlyGetsAndSets, ContainsData;

    public $id;
    public $product;
    public $variant;
    public $quantity;
    public $total;

    public function __construct()
    {
        $this->data = collect();
    }

    public function id($id = null)
    {
        return $this
            ->fluentlyGetOrSet('id')
            ->args(func_get_args());
    }

    public function product($product = null)
    {
        return $this
            ->fluentlyGetOrSet('product')
            ->setter(function ($product) {
                if (! $product instanceof Product) {
                    $product = ProductFacade::find($product);
                }

                return $product;
            })
            ->args(func_get_args());
    }

    public function variant($variant = null)
    {
        return $this
            ->fluentlyGetOrSet('variant')
            ->args(func_get_args());
    }

    public function quantity($quantity = null)
    {
        return $this
            ->fluentlyGetOrSet('quantity')
            ->args(func_get_args());
    }

    public function total($total = null)
    {
        return $this
            ->fluentlyGetOrSet('total')
            ->args(func_get_args());
    }

    public function totalIncludingTax(): int
    {
        return $this->total() + Arr::get($this->data()->get('tax'), 'amount', 0);
    }

    public function toArray(): array
    {
        return $this->data()
            ->merge([
                'id' => $this->id,
                'product' => $this->product->id(),
                'variant' => $this->variant,
                'quantity' => $this->quantity,
                'total' => $this->total,
            ])
            ->filter()
            ->all();
    }
}
