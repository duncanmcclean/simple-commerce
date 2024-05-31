<?php

namespace DuncanMcClean\SimpleCommerce\Widgets;

use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Statamic\Widgets\Widget;

class LowStockProducts extends Widget
{
    public function html()
    {
        $indexUrl = null;
        $lowStockProducts = null;

        $indexUrl = cp_route('collections.show', SimpleCommerce::productDriver()['collection']);

        $lowStockProducts = Product::query()
            ->orderBy('stock', 'asc')
            ->get()
            ->reject(fn ($product) => $product->stock() === null)
            ->take($this->config('limit', 5))
            ->map(function ($product) {
                return [
                    'id' => $product->id(),
                    'title' => $product->get('title'),
                    'stock' => $product->stock(),
                    'edit_url' => $product->entry()->editUrl(),
                ];
            });

        return view('simple-commerce::cp.widgets.low-stock-products', [
            'url' => $indexUrl,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
