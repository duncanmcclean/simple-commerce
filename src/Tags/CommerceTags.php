<?php

namespace Damcclean\Commerce\Tags;

use Damcclean\Commerce\Models\Currency;
use Damcclean\Commerce\Models\Product;
use Damcclean\Commerce\Models\ProductCategory;
use Statamic\Tags\Tags;

class CommerceTags extends Tags
{
    protected static $handle = 'commerce';

    public function currencyCode()
    {
        return Currency::where('primary', true)->first()->iso;
    }

    public function currencySymbol()
    {
        return Currency::where('primary', true)->first()->symbol;
    }

    public function stripeKey()
    {
        return config('commerce.stripe.key');
    }

    public function route()
    {
        return config("commerce.routes.{$this->getParam('key')}");
    }

    public function categories()
    {
        $categories = ProductCategory::all();

        if ($this->getParam('count')) {
            return $categories->count();
        }

        return $categories
            ->map(function ($category) {
                return $category;
            })
            ->toArray();
    }

    public function products()
    {
        $products = Product::all();

        if ($categorySlug = $this->getParam('category')) {
            $category = ProductCategory::where('slug', $categorySlug)->first();

            $products = Product::where('product_category_id', $category);
        }

        if ($this->getParam('count')) {
            return $products->count();
        }

        if (! $this->getParam('show_disabled')) {
            $products = $products
                ->reject(function ($product) {
                    return ! $product->is_enabled;
                });
        }

        return $products
            ->map(function ($product) {
                return array_merge($product->toArray(), [
                    'url' => route('products.show', ['product' => $product['slug']]),
                    'variants' => $product->variants->toArray(),
                    'from_price' => $product->variants->sortByDesc('price')->first()->price,
                ]);
            });
    }
}
