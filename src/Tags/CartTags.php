<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use Illuminate\Support\Facades\Session;
use Statamic\Facades\Entry;

class CartTags extends SubTag
{
    use Concerns\FormBuilder;

    public function index()
    {
        return Cart::find(Session::get(config('simple-commerce.cart_key')))->entry()->toAugmentedArray();
    }

    public function has()
    {
        if (! Session::has(config('simple-commerce.cart_key'))) {
            return false;
        }

        if (! Entry::find(Session::get(config('simple-commerce.cart_key')))) {
            return false;
        }

        return true;
    }

    public function items()
    {
        return Cart::find(Session::get(config('simple-commerce.cart_key')))->entry()->toAugmentedArray()['items']->value();
    }

    public function count()
    {
        if (! Session::has(config('simple-commerce.cart_key'))) {
            return 0;
        }

        return Cart::find(Session::get(config('simple-commerce.cart_key')))->count();
    }

    public function total()
    {
        return Cart::find(Session::get(config('simple-commerce.cart_key')))->entry()->toAugmentedArray()['grand_total']->value();
    }

    public function grandTotal()
    {
        return Cart::find(Session::get(config('simple-commerce.cart_key')))->entry()->toAugmentedArray()['grand_total']->value();
    }

    public function itemsTotal()
    {
        return Cart::find(Session::get(config('simple-commerce.cart_key')))->entry()->toAugmentedArray()['items_total']->value();
    }

    public function shippingTotal()
    {
        return Cart::find(Session::get(config('simple-commerce.cart_key')))->entry()->toAugmentedArray()['shipping_total']->value();
    }

    public function taxTotal()
    {
        return Cart::find(Session::get(config('simple-commerce.cart_key')))->entry()->toAugmentedArray()['tax_total']->value();
    }

    public function couponTotal()
    {
        return Cart::find(Session::get(config('simple-commerce.cart_key')))->entry()->toAugmentedArray()['coupon_total']->value();
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
                'item' => $this->getParam('item'),
            ]),
            [],
            'POST'
        );
    }

    public function removeItem()
    {
        return $this->createForm(
            route('statamic.simple-commerce.cart-items.destroy', [
                'item' => $this->getParam('item'),
            ]),
            [],
            'DELETE'
        );
    }

    public function update()
    {
        return $this->createForm(
            route('statamic.simple-commerce.cart.update'),
            [],
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
}
