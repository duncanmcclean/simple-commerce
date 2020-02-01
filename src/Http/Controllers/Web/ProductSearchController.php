<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Web;

use DoubleThreeDigital\SimpleCommerce\Models\Product;
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
        // TODO: validation request

        $query = $request->input('query');

        $results = Product::with('variants')
            ->get()
            ->where('is_enabled', true)
            ->filter(function ($item) use ($query) {
                return false !== stristr((string) $item['title'], $query);
            });

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
