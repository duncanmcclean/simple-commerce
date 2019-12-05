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
            $results = Product::all();
        } else {
            $results = Product::all()
                ->filter(function ($item) use ($query) {
                    return false !== stristr((string) $item['title'], $query);
                });
        }

        return response()->json([
            'data' => $results,
            'links' => [],
            'meta' => [
                'path' => cp_route('products.search'),
                'sortColumn' => 'title',
            ]
        ]);
    }
}
