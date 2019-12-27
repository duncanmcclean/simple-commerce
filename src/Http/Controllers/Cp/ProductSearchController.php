<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Facades\Product;
use Statamic\Http\Controllers\CP\CpController;

class ProductSearchController extends CpController
{
    public function __invoke()
    {
        $query = request()->input('search');

        if (! $query) {
            $results = Product::all()
                ->map(function ($product) {
                    return array_merge($product->toArray(), [
                        'edit_url' => cp_route('products.edit', ['product' => $product['id']]),
                        'delete_url' => cp_route('products.destroy', ['product' => $product['id']]),
                    ]);
                });
        } else {
            $results = Product::all()
                ->filter(function ($item) use ($query) {
                    return false !== stristr((string) $item['title'], $query);
                })
                ->map(function ($product) {
                    return array_merge($product->toArray(), [
                        'edit_url' => cp_route('products.edit', ['product' => $product['id']]),
                        'delete_url' => cp_route('products.destroy', ['product' => $product['id']]),
                    ]);
                });
        }

        return response()->json([
            'data' => $results,
            'links' => [],
            'meta' => [
                'path' => cp_route('products.search'),
                'sortColumn' => 'title',
            ],
        ]);
    }
}
