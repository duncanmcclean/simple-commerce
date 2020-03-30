<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Helpers\FormBuilder;
use DoubleThreeDigital\SimpleCommerce\Helpers\Currency as CurrencyHelper;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use DoubleThreeDigital\SimpleCommerce\Models\State;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Tags\Tags;

class SimpleCommerceTag extends Tags
{
    protected static $handle = 'simple-commerce';

    public function currencyCode()
    {
        return (new CurrencyHelper())->iso();
    }

    public function currencySymbol()
    {
        return (new CurrencyHelper())->symbol();
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
        $products = Product::with('variants', 'productCategory', 'attributes')->get();

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

        if ($this->getParam('first')) {
            return $products->first()->toArray();
        }

        return $products->toArray();
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

    public function form()
    {
        return (new FormBuilder())->build($this->getParam('for'), collect($this->params)->toArray(), $this->parse());
    }

    public function errors()
    {
        if (! (new FormBuilder())->hasErrors()) {
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
