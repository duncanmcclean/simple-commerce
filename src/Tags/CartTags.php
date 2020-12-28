<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\SessionCart;

class CartTags extends SubTag
{
    use Concerns\FormBuilder,
        SessionCart;

    public function index()
    {
        return $this->getOrMakeSessionCart()->entry()->toAugmentedArray();
    }

    public function has()
    {
        return $this->hasSessionCart();
    }

    public function items()
    {
        $cart = $this->getOrMakeSessionCart();

        return isset($cart->data['items']) && $cart->data['items'] != [] ?
            $cart->entry()->toAugmentedArray()['items']->value() :
            [];
    }

    public function count()
    {
        if (! $this->hasSessionCart()) {
            return 0;
        }

        return collect($this->getSessionCart()->data['items'])->count();
    }

    public function total()
    {
        if ($this->hasSessionCart()) {
            return $this->getSessionCart()->entry()->toAugmentedArray()['grand_total']->value();
        }

        return 0;
    }

    public function grandTotal()
    {
        if ($this->hasSessionCart()) {
            return $this->getSessionCart()->entry()->toAugmentedArray()['grand_total']->value();
        }

        return 0;
    }

    public function itemsTotal()
    {
        if ($this->hasSessionCart()) {
            return $this->getSessionCart()->entry()->toAugmentedArray()['items_total']->value();
        }

        return 0;
    }

    public function shippingTotal()
    {
        if ($this->hasSessionCart()) {
            return $this->getSessionCart()->entry()->toAugmentedArray()['shipping_total']->value();
        }

        return 0;
    }

    public function taxTotal()
    {
        if ($this->hasSessionCart()) {
            return $this->getSessionCart()->entry()->toAugmentedArray()['tax_total']->value();
        }

        return 0;
    }

    public function couponTotal()
    {
        if ($this->hasSessionCart()) {
            return $this->getSessionCart()->entry()->toAugmentedArray()['coupon_total']->value();
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
        $cart = $this->getSessionCart();

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
        $cart = $this->getSessionCart();

        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        if (! $cart->has($method)) {
            return $cart->get($method);
        }

        return null;
    }
}
