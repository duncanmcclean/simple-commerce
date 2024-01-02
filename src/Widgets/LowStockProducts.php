<?php

namespace DoubleThreeDigital\SimpleCommerce\Widgets;

use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Products\EntryProductRepository;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Facades\Collection;
use Statamic\Widgets\Widget;

class LowStockProducts extends Widget
{
    public function html()
    {
        $indexUrl = null;
        $lowStockProducts = null;

        if ((new self)->isOrExtendsClass(SimpleCommerce::productDriver()['repository'], EntryProductRepository::class)) {
            $indexUrl = cp_route('collections.show', SimpleCommerce::productDriver()['collection']);

            $lowStockProducts = Collection::find(SimpleCommerce::productDriver()['collection'])
                ->queryEntries()
                ->orderBy('stock', 'asc')
                ->get()
                ->reject(function ($entry) {
                    return $entry->has('product_variants') || ! $entry->has('stock');
                })
                ->take($this->config('limit', 5))
                ->map(function ($entry) {
                    return Product::find($entry->id());
                })
                ->values()
                ->map(function ($product) {
                    return [
                        'id' => $product->id(),
                        'title' => $product->get('title'),
                        'stock' => $product->stock(),
                        'edit_url' => $product->resource()->editUrl(),
                    ];
                });
        }

        if (! $lowStockProducts) {
            throw new \Exception('Low Stock Products widget is not compatible with the configured products repository.');
        }

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
