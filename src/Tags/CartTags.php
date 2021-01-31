<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;

class CartTags extends SubTag
{
    use Concerns\FormBuilder,
        CartDriver;

    public function index()
    {
        return $this->getOrMakeCart()->entry()->toAugmentedArray();
    }

    public function has()
    {
        return $this->hasCart();
    }

    public function items()
    {
        $cart = $this->getOrMakeCart();

        return isset($cart->data['items']) && $cart->data['items'] != [] ?
            $cart->entry()->toAugmentedArray()['items']->value() :
            [];
    }

    public function count()
    {
        if (! $this->hasCart()) {
            return 0;
        }

        if (! $this->hasCart()) {
            return 0;
        }

        return collect($this->getCart()->get('items'))->count();
    }

    public function total()
    {
        if ($this->hasCart()) {
            return $this->getCart()->entry()->toAugmentedArray()['grand_total']->value();
        }

        return 0;
    }

    public function grandTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->entry()->toAugmentedArray()['grand_total']->value();
        }

        return 0;
    }

    public function itemsTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->entry()->toAugmentedArray()['items_total']->value();
        }

        return 0;
    }

    public function shippingTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->entry()->toAugmentedArray()['shipping_total']->value();
        }

        return 0;
    }

    public function taxTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->entry()->toAugmentedArray()['tax_total']->value();
        }

        return 0;
    }

    public function couponTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->entry()->toAugmentedArray()['coupon_total']->value();
        }

        return 0;
    }

    public function addItem()
    {
        return $this->createForm(
            route('statamic.simple-commerce.cart-items.store'),
            [],
            'POST'
        );
    }

    public function updateItem()
    {
        return $this->createForm(
            route('statamic.simple-commerce.cart-items.update', [
                'item' => $this->params->get('item'),
            ]),
            [],
            'POST'
        );
    }

    public function removeItem()
    {
        return $this->createForm(
            route('statamic.simple-commerce.cart-items.destroy', [
                'item' => $this->params->get('item'),
            ]),
            [],
            'DELETE'
        );
    }

    public function update()
    {
        $cart = $this->getCart();

        return $this->createForm(
            route('statamic.simple-commerce.cart.update'),
            $cart->entry()->toAugmentedArray(),
            'POST'
        );
    }

    public function empty()
    {
        return $this->createForm(
            route('statamic.simple-commerce.cart.empty'),
            [],
            'DELETE'
        );
    }

    public function wildcard($method)
    {
        $cart = $this->getCart();

        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        if (! $cart->has($method)) {
            return $cart->get($method);
        }

        return null;
    }
}
