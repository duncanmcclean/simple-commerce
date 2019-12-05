<?php

namespace Damcclean\Commerce\Tags;

use Damcclean\Commerce\Facades\Product;
use Statamic\Tags\Tags;

class ProductTags extends Tags
{
    protected static $handle = 'products';

    public function index()
    {
        return Product::all();
    }

    public function count()
    {
        return collect(Product::all())->count();
    }
}
