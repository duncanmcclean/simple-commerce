<?php

namespace Damcclean\Commerce\Tags;

use Facades\Damcclean\Commerce\Models\Product;
use Statamic\Tags\Tags;

class ProductTags extends Tags
{
    protected static $handle = 'products';

    public function index()
    {
        $products = Product::all();

        return $products;
    }

    public function count()
    {
        $items = Product::all();
        return collect($items)->count();
    }
}
