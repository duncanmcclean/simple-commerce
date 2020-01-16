<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use DoubleThreeDigital\SimpleCommerce\Models\State;
use Statamic\Tags\Tags;

class CommerceTags extends Tags
{
    protected static $handle = 'commerce';

    public function currencyCode()
    {
        return Currency::where('iso', config('commerce.currency'))->first()->iso;
    }

    public function currencySymbol()
    {
        return Currency::where('iso', config('commerce.currency'))->first()->symbol;
    }

    public function stripeKey()
    {
        return config('commerce.stripe.key');
    }

    public function route()
    {
        return config("commerce.{$this->getParam('key')}");
    }

    public function categories()
    {
        $categories = ProductCategory::all();

        if ($this->getParam('count')) {
            return $categories->count();
        }

        return $categories
            ->map(function ($category) {
                return array_merge($category->toArray(), [
                    'url' => route('categories.show', ['category' => $category->slug]),
                ]);
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

        if (! $this->getParam('include_disabled')) {
            $products = $products
                ->reject(function ($product) {
                    return ! $product->is_enabled;
                });
        }

        if ($this->getParam('count')) {
            return $products->count();
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

    public function countries()
    {
        return Country::all();
    }

    public function states()
    {
        $states = State::all();

        if ($this->getParam('country')) {
            $states = $states->where('country_id', Country::where('iso', $this->getParam('country')))->get();
        }

        if ($this->getParam('count')) {
            return $states->count();
        }

        return $states;
    }

    public function currencies()
    {
        return Currency::all();
    }
}
