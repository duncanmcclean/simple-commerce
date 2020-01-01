<?php

namespace Damcclean\Commerce\Http\Controllers\Web;

use Damcclean\Commerce\Models\Product;
use Statamic\View\View;

class ProductController extends Controller
{
    public function index()
    {
        return (new View)
            ->template('commerce::web.products')
            ->layout('commerce::web.layout')
            ->with(['title' => 'Products']);
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)->first();

        return (new View)
            ->template('commerce::web.product')
            ->layout('commerce::web.layout')
            ->with($product->toArray());
    }
}
