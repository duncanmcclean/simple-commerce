<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Currency as FacadesCurrency;
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
        $products = Product::get();

        if ($this->getParam('category') != null) {
            $category = ProductCategory::select('id')->where('slug', $this->getParam('category'))->first();
            $products = $products->where('product_category_id', $category->id);
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
            $newProduct = $product->toArray();
            $newProduct['images'] = [];

            $product->attributes->each(function (Attribute $attribute) use (&$newProduct) {
                $newProduct["$attribute->key"] = $attribute->value;
            });

            $newProduct['variants'] = $product->variants->map(function (Variant $variant) use (&$newProduct) {
                $newVariant = $variant->toArray();

                collect($variant->images)->each(function ($image) use (&$newProduct) {
                    $newProduct['images'][] = $image;
                });

                $variant->attributes->each(function (Attribute $attribute) use (&$newVariant) {
                    $newVariant["$attribute->key"] = $attribute->value;
                });

                return $newVariant;
            })->toArray();

            return $newProduct;
        });

        if ($this->getBool('first')) {
            return $products->first();
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
        $productArray['images'] = [];

        $product->attributes->each(function (Attribute $attribute) use (&$productArray) {
            $productArray["$attribute->key"] = $attribute->value;
        });

        $productArray['variants'] = $product->variants->map(function (Variant $variant) use (&$productArray) {
            $variantArray = $variant->toArray();

            collect($variant->images)->each(function ($image) use (&$productArray) {
                $productArray['images'][] = $image;
            });

            $variant->attributes->each(function (Attribute $attribute) use (&$variantArray) {
                $variantArray["$attribute->key"] = $attribute->value;
            });

            $variantArray['product'] = $productArray;

            return $variantArray;
        })->toArray();

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
            return null;
        }

        if ($this->getParam('get')) {
            $order = auth()->user()->orders()
                ->where('uuid', $this->getParam('get'))
                ->with('orderStatus', 'billingAddress', 'shippingAddress', 'currency', 'customer', 'lineItems')
                ->first()
                ->toArray();

            $order['line_items'] = collect($order['line_items'])
                ->map(function ($data) {
                    $variant = function () use ($data) {
                        $originalVariant = Variant::find($data['variant_id']);
                        
                        $variant = $originalVariant->toArray();
                        
                        collect($originalVariant->images)->each(function ($image) use (&$variant) {
                            $variant['images'][] = $image;
                        });

                        $originalVariant->attributes->each(function (Attribute $attribute) use (&$variant) {
                            $variant["$attribute->key"] = $attribute->value;
                        });

                        return $variant;
                    };

                    $data['variant'] = $variant();

                    $product = function () use ($data) {
                        $originalProduct = Product::find($data['variant']['product_id']);

                        $product = $originalProduct->toArray();
                        $product['images'] = [];

                        $originalProduct->attributes->each(function (Attribute $attribute) use (&$product) {
                            $product["$attribute->key"] = $attribute->value;
                        });

                        return $product;
                    };
                    
                    $data['product'] = $product();
                    $data['total'] = FacadesCurrency::parse($data['total']);

                    return $data;
                })
                ->toArray();

            $order['item_total'] = FacadesCurrency::parse($order['item_total']);
            $order['shipping_total'] = FacadesCurrency::parse($order['shipping_total']);
            $order['tax_total'] = FacadesCurrency::parse($order['tax_total']);
            $order['coupon_total'] = FacadesCurrency::parse($order['coupon_total']);
            $order['total'] = FacadesCurrency::parse((float) $order['total']);

            return $order;    
        }

        $orders = auth()->user()->orders()
            ->with('orderStatus', 'billingAddress', 'shippingAddress', 'currency', 'customer', 'lineItems')
            ->get();

        if ($this->getParam('count')) {
            return $orders->count();
        }

        return $orders->toArray();
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

    protected function hasParam(string $param)
    {
        if (isset($this->params[$param])) {
            return true;
        }

        return false;
    }
}