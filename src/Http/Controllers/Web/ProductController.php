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
            ->with(['title' => 'Products']);
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->with('variants')
            ->first();

        if (! $product->is_enabled) {
            abort(404);
        }

        return (new View)
            ->template('commerce::web.product')
            ->layout('commerce::web.layout')
            ->with(array_merge($product->toArray(), [
                'from_price' => collect($product->variants)->sortByDesc('price')->first()->price
            ]));
    }
}
