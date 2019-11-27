<?php

namespace Damcclean\Commerce\Http\Controllers\Web;

use Facades\Damcclean\Commerce\Models\Product;
use Statamic\View\View;

class ProductController extends Controller
{
    public function index()
    {
        return (new View)
            ->template('commerce.products')
            ->layout('layout')
            ->with([]);
    }

    public function show($product)
    {
        // WIP don't allow here if the product is not enabled

        $product = Product::get($product);

        return (new View)
            ->template('commerce.product')
            ->layout('layout')
            ->with((array) $product);
    }
}
