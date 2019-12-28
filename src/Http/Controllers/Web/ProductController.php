<?php

namespace Damcclean\Commerce\Http\Controllers\Web;

use Damcclean\Commerce\Facades\Product;
use Statamic\Facades\Asset;
use Statamic\View\View;

class ProductController extends Controller
{
    public function index()
    {
        return (new View)
            ->template('commerce::web.products')
            ->layout('commerce::web.layout');
    }

    public function show(string $product)
    {
        $product = Product::findBySlug($product);

        if (isset($product['enabled']) == false) {
            if (auth()->check() == false) {
                abort(404);
            }
        }

        if (isset($product['gallery'][0])) {
            $product['gallery'] = collect($product['gallery'])
                ->map(function ($asset) {
                    return Asset::findById($asset)->url();
                });
        }

        return (new View)
            ->template('commerce::web.product')
            ->layout('commerce::web.layout')
            ->with($product->toArray());
    }
}
