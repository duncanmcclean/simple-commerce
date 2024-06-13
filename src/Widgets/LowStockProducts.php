<?php

namespace DuncanMcClean\SimpleCommerce\Widgets;

use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Products\ProductType;
use DuncanMcClean\SimpleCommerce\Products\ProductVariant;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Statamic\Widgets\Widget;

class LowStockProducts extends Widget
{
    public function html()
    {
        $indexUrl = cp_route('collections.show', SimpleCommerce::productDriver()['collection']);

        $standardLowStockProducts = Product::query()
            ->wherePurchaseableType(ProductType::Product)
            ->orderBy('stock', 'asc')
            ->get()
            ->reject(fn ($product) => $product->stock() === null)
            ->take($this->config('limit', 5))
            ->map(function ($product) {
                return [
                    'id' => $product->id(),
                    'title' => $product->get('title'),
                    'stock' => $product->stock(),
                    'edit_url' => $product->resource()->editUrl(),
                ];
            });

        $variantProductsWithLowStock = Product::query()
            ->wherePurchaseableType(ProductType::Variant)
            ->get()
            ->flatMap->variantOptions()
            ->reject(fn ($variant) => $variant->stock() === null)
            ->sortBy(fn ($variant) => $variant->stock())
            ->take($this->config('limit', 5))
            ->map(function (ProductVariant $variant) {
                return [
                    'id' => $variant->key(),
                    'title' => "{$variant->product()->get('title')} - {$variant->name()}",
                    'stock' => $variant->stock(),
                    'edit_url' => $variant->product()->resource()->editUrl(),
                ];
            });

        return view('simple-commerce::cp.widgets.low-stock-products', [
            'url' => $indexUrl,
            'lowStockProducts' => collect($standardLowStockProducts)->merge($variantProductsWithLowStock),
        ]);
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
