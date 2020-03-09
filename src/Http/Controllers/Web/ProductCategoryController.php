<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Web;

use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use Statamic\View\View;

class ProductCategoryController extends Controller
{
    public function show(string $slug)
    {
        $category = ProductCategory::where('slug', $slug)->first();

        if (! $category->title) {
            abort(404);
        }

        $products = Product::with('variants')
            ->get()
            ->where('product_category_id', $category->id)
            ->reject(function ($product) {
                return ! $product->is_enabled;
            });

        return (new View)
            ->template('simple-commerce::web.category')
            ->layout('simple-commerce::web.layout')
            ->with([
                'title' => $category->title,
                'products' => $products,
            ]);
    }
}
