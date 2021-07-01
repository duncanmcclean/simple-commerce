<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;

class CartTags extends SubTag
{
    use Concerns\FormBuilder;
    use CartDriver;

    public function index()
    {
        return $this->getOrMakeCart()->toAugmentedArray();
    }

    public function has()
    {
        return $this->hasCart();
    }

    public function items()
    {
        $cart = $this->getOrMakeCart();

        return $cart->lineItems()->count() >= 1
            ? $cart->toAugmentedArray()['items']->value()
            : [];
    }

    public function count()
    {
        if (!$this->hasCart()) {
            return 0;
        }

        return $this->getCart()->lineItems()->count();
    }

    public function total()
    {
        return $this->grandTotal();
    }

    public function grandTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->toAugmentedArray()['grand_total']->value();
        }

        return 0;
    }

    public function rawGrandTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->get('grand_total');
        }

        return 0;
    }

    public function itemsTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->toAugmentedArray()['items_total']->value();
        }

        return 0;
    }

    public function rawItemsTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->get('items_total');
        }

        return 0;
    }

    public function shippingTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->toAugmentedArray()['shipping_total']->value();
        }

        return 0;
    }

    public function rawShippingTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->get('shipping_total');
        }

        return 0;
    }

    public function taxTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->toAugmentedArray()['tax_total']->value();
        }

        return 0;
    }

    public function rawTaxTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->get('tax_total');
        }

        return 0;
    }

    public function couponTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->toAugmentedArray()['coupon_total']->value();
        }

        return 0;
    }

    public function rawCouponTotal()
    {
        if ($this->hasCart()) {
            return $this->getCart()->get('coupon_total');
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
            $cart->toAugmentedArray(),
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

        if (property_exists($cart, $method)) {
            return $cart->{$method};
        }

        if ($cart->has($method)) {
            return $cart->get($method);
        }

        return null;
    }
}
