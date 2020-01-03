<?php

namespace Damcclean\Commerce\Http\Controllers\Web;

use Damcclean\Commerce\Models\Product;
use Damcclean\Commerce\Models\ProductCategory;
use Statamic\View\View;

class ProductCategoryController extends Controller
{
    public function show(string $slug)
    {
        $category = ProductCategory::where('slug', $slug)->first();

        $products = Product::all()
            ->where('product_category_id', $category->id)
            ->reject(function ($product) {
                return ! $product->is_enabled;
            })
            ->map(function ($product) {
                return array_merge($product->toArray(), [
                    'url' => route('products.show', ['product' => $product['slug']]),
                    'variants' => $product->variants->toArray(),
                    'from_price' => $product->variants->sortByDesc('price')->first()->price,
                ]);
            });

        return (new View)
            ->template('commerce::web.category')
            ->layout('commerce::web.layout')
            ->with([
                'title' => $category['title'],
                'products' => $products,

            ]);
    }
}
