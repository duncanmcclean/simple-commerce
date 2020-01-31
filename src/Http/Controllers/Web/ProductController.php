<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Web;

use DoubleThreeDigital\SimpleCommerce\Models\Product;
use Statamic\View\View;

class ProductController extends Controller
{
    public function index()
    {
        return (new View)
            ->template('commerce::web.products')
            ->layout('commerce::web.layout')
            ->with([
                'title' => 'Products',
                'products' => Product::with('variants')->get(),
            ]);
    }

    public function show(string $slug)
    {
        $product = Product::with('variants')
            ->where('slug', $slug)
            ->first();

        if (! $product || ! $product->is_enabled) {
            abort(404);
        }

        return (new View)
            ->template('commerce::web.product')
            ->layout('commerce::web.layout')
            ->with($product->toArray());
    }
}
