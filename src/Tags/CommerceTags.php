<?php

namespace Damcclean\Commerce\Tags;

use Damcclean\Commerce\Models\Product;
use Damcclean\Commerce\Models\ProductCategory;
use Statamic\Tags\Tags;

class CommerceTags extends Tags
{
    protected static $handle = 'commerce';

    public function currencyCode()
    {
        return config('commerce.currency.code');
    }

    public function currencySymbol()
    {
        return config('commerce.currency.symbol');
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
        return ProductCategory::all()
            ->map(function ($category) {
                return $category;
            })
            ->toArray();
    }

    public function products()
    {
        if ($this->getParam('count')) {
            return Product::all()->count();
        }

        return Product::all()
            ->map(function ($product) {
                return array_merge($product->toArray(), [
                    'url' => route('products.show', ['product' => $product['slug']]),
                    'variants' => $product->variants->toArray(),
                    'from_price' => $product->variants->sortByDesc('price')->first()->price,
                ]);
            });
    }
}
