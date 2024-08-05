<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use DuncanMcClean\SimpleCommerce\Contracts\Products\Product as ProductContract;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Products\ProductType;
use DuncanMcClean\SimpleCommerce\Products\ProductVariant;
use Statamic\Contracts\Data\Augmented;
use Statamic\Data\ContainsData;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class LineItem
{
    use FluentlyGetsAndSets, ContainsData, HasAugmentedInstance;

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

    public function variant($variant = null)
    {
        return $this
            ->fluentlyGetOrSet('variant')
            ->getter(function ($variant) {
                if (! $variant) {
                    return null;
                }

                return $this->product()->variant($variant);
            })
            ->setter(function ($variant) {
                if ($variant instanceof ProductVariant) {
                    return $variant->id();
                }

                return $variant;
            })
            ->args(func_get_args());
    }

    public function quantity($quantity = null)
    {
        return $this
            ->fluentlyGetOrSet('quantity')
            ->args(func_get_args());
    }

    // TODO: We should really be storing the unit price, not just the total
    // (otherwise, things will get messed up when the product's price gets updated).
    public function unitPrice()
    {
        return $this->variant
            ? $this->variant()->price()
            : $this->product()->price();
    }

    public function total($total = null)
    {
        return $this
            ->fluentlyGetOrSet('total')
            ->args(func_get_args());
    }

    public function defaultAugmentedArrayKeys()
    {
        return [];
    }

    public function shallowAugmentedArrayKeys()
    {
        return ['id', 'product', 'variant', 'quantity', 'total'];
    }

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedLineItem($this);
    }

    public function toArray(): array
    {
        return $this->data()
            ->merge([
                'id' => $this->id,
                'product' => $this->product()->id(),
                'variant' => $this->variant,
                'quantity' => $this->quantity,
                'total' => $this->total,
            ])
            ->filter()
            ->all();
    }
}
