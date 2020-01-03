<?php

namespace Damcclean\Commerce\Http\Controllers\Web;

use Damcclean\Commerce\Models\Product;
use Illuminate\Http\Request;
use Statamic\View\View;

class ProductSearchController
{
    public function index()
    {
        return (new View)
            ->template('commerce::web.search')
            ->layout('commerce::web.layout')
            ->with(['title' => 'Search']);
    }

    public function show(Request $request)
    {
        $query = $request->input('query');

        if (! $query) {
            $results = Product::all()
                ->map(function ($product) {
                    return array_merge($product->toArray(), [
                        'url' => route('products.show', ['product' => $product['slug']]),
                        'variants' => $product->variants->toArray(),
                        'from_price' => $product->variants->sortByDesc('price')->first()->price,
                    ]);
                });
        } else {
            $results = Product::all()
                ->filter(function ($item) use ($query) {
                    return false !== stristr((string) $item['title'], $query);
                })
                ->map(function ($product) {
                    return array_merge($product->toArray(), [
                        'url' => route('products.show', ['product' => $product['slug']]),
                        'variants' => $product->variants->toArray(),
                        'from_price' => $product->variants->sortByDesc('price')->first()->price,
                    ]);
                });
        }

        return (new View)
            ->template('commerce::web.search')
            ->layout('commerce::web.layout')
            ->with([
                'results' => $results,
                'count' => $results->count(),
                'query' => $query,
            ]);
    }
}
