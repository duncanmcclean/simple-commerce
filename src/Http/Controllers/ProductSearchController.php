<?php

namespace Damcclean\Commerce\Http\Controllers;

use Facades\Damcclean\Commerce\Models\Product;
use Statamic\Http\Controllers\CP\CpController;

class ProductSearchController extends CpController
{
    public function __invoke()
    {
        $results = Product::search(request()->input('search'));

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
