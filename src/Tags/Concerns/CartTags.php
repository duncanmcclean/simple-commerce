<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags\Concerns;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use Illuminate\Support\Facades\Session;

trait CartTags
{
    public function cart()
    {
        return Cart::find(Session::get('simple-commerce-cart'))->entry()->toAugmentedArray();
    }

    public function cartItems()
    {
        return Cart::find(Session::get('simple-commerce-cart'))->entry()->toAugmentedArray()['items']->value();
    }

    public function cartItemsCount()
    {
        if (! Session::has('simple-commerce-cart')) {
            return 0;
        }

        return Cart::find(Session::get('simple-commerce-cart'))->count();
    }

    public function total()
    {
        return Cart::find(Session::get('simple-commerce-cart'))->entry()->toAugmentedArray()['grand_total']->value();
    }

    public function grandTotal()
    {
        return Cart::find(Session::get('simple-commerce-cart'))->entry()->toAugmentedArray()['grand_total']->value();
    }

    public function itemsTotal()
    {
        return Cart::find(Session::get('simple-commerce-cart'))->entry()->toAugmentedArray()['items_total']->value();
    }

    public function shippingTotal()
    {
        return Cart::find(Session::get('simple-commerce-cart'))->entry()->toAugmentedArray()['shipping_total']->value();
    }

    public function taxTotal()
    {
        return Cart::find(Session::get('simple-commerce-cart'))->entry()->toAugmentedArray()['tax_total']->value();
    }

    public function couponTotal()
    {
        return Cart::find(Session::get('simple-commerce-cart'))->entry()->toAugmentedArray()['coupon_total']->value();
    }

    public function addCartItem()
    {
        return $this->createForm(
            route('statamic.simple-commerce.cart.store'),
            [],
            'POST'
        );
    }

    public function updateCartItem()
    {
        return $this->createForm(
            route('statamic.simple-commerce.cart.update', [
                'item' => $this->getParam('item'),
            ]),
            [],
            'POST'
        );
    }

    public function removeCartItem()
    {
        return $this->createForm(
            route('statamic.simple-commerce.cart.destroy', [
                'item' => $this->getParam('item'),
            ]),
            [],
            'DELETE'
        );
    }

    public function emptyCart()
    {
        return $this->createForm(
            route('statamic.simple-commerce.cart.empty'),
            [],
            'DELETE'
        );
    }
}