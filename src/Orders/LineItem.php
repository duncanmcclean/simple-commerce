<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use DuncanMcClean\SimpleCommerce\Contracts\Products\Product;
use DuncanMcClean\SimpleCommerce\Facades\Product as ProductFacade;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class LineItem
{
    use FluentlyGetsAndSets;

    public $id;
    public $product;
    public $variant;
    public $quantity;
    public $total;
    public $tax;
    public $metadata;

    public function __construct()
    {
        $this->metadata = collect();
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
        return $this->total() + $this->tax()['amount'];
    }

    public function tax($tax = null)
    {
        return $this
            ->fluentlyGetOrSet('tax')
            ->args(func_get_args());
    }

    public function metadata($metadata = null)
    {
        return $this
            ->fluentlyGetOrSet('metadata')
            ->setter(function ($metadata) {
                if (is_array($metadata)) {
                    $metadata = collect($metadata);
                }

                return $metadata;
            })
            ->args(func_get_args());
    }

    public function toArray(): array
    {
        // todo: null values shouldn't be returned
        return array_merge($this->metadata->all(), [
            'id' => $this->id,
            'product' => $this->product->id(),
            'variant' => $this->variant,
            'quantity' => $this->quantity,
            'total' => $this->total,
            'tax' => $this->tax,
        ]);
    }
}
