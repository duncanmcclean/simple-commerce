<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\FormBuilder;
use DoubleThreeDigital\SimpleCommerce\Models\Attribute;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use DoubleThreeDigital\SimpleCommerce\Models\State;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Facades\Auth;
use Statamic\Tags\Tags;

class SimpleCommerceTag extends Tags
{
    protected static $handle = 'simple-commerce';

    public function currencyCode()
    {
        return \DoubleThreeDigital\SimpleCommerce\Facades\Currency::iso();
    }

    public function currencySymbol()
    {
        return \DoubleThreeDigital\SimpleCommerce\Facades\Currency::symbol();
    }

    public function categories()
    {
        $categories = ProductCategory::all();

        if ($this->getParam('count')) {
            return $categories->count();
        }

        return $categories->toArray();
    }

    public function products()
    {
        $products = Product::with('productCategory')->get();

        if ($this->getParam('category') != null) {
            $category = ProductCategory::where('slug', $this->getParam('category'))->first();
            $products = $products->where('product_category_id', $category->id);
        }

        if ($where = $this->getParam('where')) {
            $key = explode(':', $where)[0];
            $value = explode(':', $where)[1];

            $products = $products->where($key, $value);
        }

        if (! $this->getParam('include_disabled')) {
            $products = $products
                ->reject(function ($product) {
                    return ! $product->is_enabled;
                });
        }

        if ($this->getParam('not')) {
            $not = $this->getParam('not');

            $products = $products
                ->reject(function ($product) use ($not) {
                    if ($product->id === $not) {
                        return true;
                    }

                    if ($product->uuid === $not) {
                        return true;
                    }

                    if ($product->slug === $not) {
                        return true;
                    }
                });
        }

        if ($this->getParam('limit')) {
            $products = $products->take($this->getParam('limit'));
        }

        if ($this->getParam('count')) {
            return $products->count();
        }

        $products = $products->map(function (Product $product) {
            $newProduct = $product->toArray();

            $product->attributes->each(function (Attribute $attribute) use (&$newProduct) {
                $newProduct["$attribute->key"] = $attribute->value;
            });

            $newProduct['variants'] = $product->variants->map(function (Variant $variant) {
                $newVariant = $variant->toArray();

                $variant->attributes->each(function (Attribute $attribute) use (&$newVariant) {
                    $newVariant["$attribute->key"] = $attribute->value;
                });

                return $newVariant;
            });

            return $newProduct;
        });

        if ($this->getParam('first')) {
            return $products->toArray()[0];
        }

        return $products->toArray();
    }

    public function product()
    {
        $slug = $this->getParam('slug');

        if (! $slug) {
            throw new \Exception('You must pass in a slug to the simple-commerce:product tag.');
        }

        $product = Product::enabled()->where('slug', $slug)->first();

        if (! $product) {
            throw new \Exception('Product Not Found');
        }

        $productArray = $product->toArray();

        $product->attributes->each(function (Attribute $attribute) use (&$productArray) {
            $productArray["$attribute->key"] = $attribute->value;
        });

        $newProduct['variants'] = $product->variants->map(function (Variant $variant) {
            $variantArray = $variant->toArray();

            $variant->attributes->each(function (Attribute $attribute) use (&$variantArray) {
                $variantArray["$attribute->key"] = $attribute->value;
            });

            return $variantArray;
        });

        return $productArray;
    }

    public function countries()
    {
        return Country::all()->toArray();
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

        return $states->toArray();
    }

    public function currencies()
    {
        return Currency::all()->toArray();
    }

    public function gateways()
    {
        return SimpleCommerce::gateways();
    }

    public function orders()
    {
        if (Auth::guest()) {
            return null;
        }

        if ($this->getParam('get')) {
            return auth()->user()->orders()
                ->where('uuid', $this->getParam('get'))
                ->with('orderStatus', 'billingAddress', 'shippingAddress', 'currency', 'customer')
                ->first()
                ->toArray();
        }

        $orders = auth()->user()->orders()
            ->with('orderStatus', 'billingAddress', 'shippingAddress', 'currency', 'customer')
            ->get();

        if ($this->getParam('count')) {
            return $orders->count();
        }

        return $orders->toArray();
    }

    public function form()
    {
        return FormBuilder::build($this->getParam('for'), collect($this->params)->toArray(), $this->parse());
    }

    public function errors()
    {
        if (! FormBuilder::hasErrors()) {
            return false;
        }

        $errors = [];

        foreach (session('errors')->getBag('form.'.$this->getParam('for'))->all() as $error) {
            $errors[]['value'] = $error;
        }

        return ($this->content === '')
            ? !empty($errors)
            : $this->parseLoop($errors);
    }

    public function success()
    {
        if (! $this->getParam('for')) {
            return false;
        }

        return session()->has("form.{$this->getParam('for')}.success");
    }
}
