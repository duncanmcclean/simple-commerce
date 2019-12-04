<?php

namespace Damcclean\Commerce\Http\Controllers\Web;

use Damcclean\Commerce\Facades\Product;
use Statamic\View\View;

class ProductController extends Controller
{
    public function index()
    {
        return (new View)
            ->template('commerce.products')
            ->layout('layout');
    }

    public function show($product)
    {
        // WIP don't allow here if the product is not enabled

        $product = Product::findBySlug($product);

        return (new View)
            ->template('commerce.product')
            ->layout('layout')
            ->with((array) $product);
    }
}
