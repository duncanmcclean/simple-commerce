<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\FormBuilder;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use DoubleThreeDigital\SimpleCommerce\Models\State;
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

        $categories = $categories->map(function (ProductCategory $category) {
            return array_merge($category->toArray(), [
                'products' => $category->products->map(function (Product $product) {
                    return $product->templatePrep();
                }),
            ]);
        });

        return $categories->toArray();
    }

    public function products()
    {
        $products = Product::get();

        if ($this->getParam('category') != null) {
            $category = ProductCategory::select('id')->where('slug', $this->getParam('category'))->first();
            $products = $category->products;
        }

        if ($this->hasParam('where')) {
            $where = $this->getParam('where');

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

        if ($this->hasParam('limit')) {
            $products = $products->take($this->getInt('limit'));
        }

        if ($this->getParam('count')) {
            return $products->count();
        }

        $products = $products->map(function (Product $product) {
            return $product->templatePrep();
        });

        if ($this->getBool('first')) {
            return $products->first()->toArray();
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

        return $product->templatePrep();
    }

    public function countries()
    {
        return Country::all()->toArray();
    }

    public function states()
    {
        $states = State::all();

        if ($this->getParam('country')) {
            $states = $states->where('country_id', Country::select('id')->where('iso', $this->getParam('country')->first()->id));
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
            return;
        }

        if ($this->getParam('get')) {
            return auth()->user()->orders()
                ->where('uuid', $this->getParam('get'))
                ->first()
                ->templatePrep();
        }

        $orders = auth()->user()->orders()->get();

        if ($this->getParam('count')) {
            return $orders->count();
        }

        return $orders
            ->each(function (Order $order) {
                return $order->templatePrep();
            })->toArray();
    }

    public function form()
    {
        return FormBuilder::build(
            $this->getParam('for') ?? $this->getParam('in'),
            collect($this->params)->toArray(),
            $this->parse()
        );
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
            ? ! empty($errors)
            : $this->parseLoop($errors);
    }

    public function success()
    {
        if (! $this->getParam('for')) {
            return false;
        }

        return session()->has("form.{$this->getParam('for')}.success");
    }

    protected function hasParam(string $param)
    {
        if (isset($this->params[$param])) {
            return true;
        }

        return false;
    }
}
