<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use Illuminate\Support\Facades\Session;
use Statamic\Tags\Tags;

class CartTag extends Tags
{
    protected static $handle = 'cart';

    public function index()
    {
        return $this->items();
    }

    public function items()
    {
        $this->dealWithSession();

        return $this->findCart()->get('line_items');
    }

    public function count()
    {
        // We don't want to create a cart for every page request with the cart:count tag on it
        if (!Session::has(config('simple-commerce.cart_session_key'))) {
            return 0;
        }

        return $this->findCart()->get('items_count');
    }

    public function total()
    {
        $cart = $this->findCart();

        if ($this->getParam('items')) {
            return $cart->get('item_total');
        }

        if ($this->getParam('shipping')) {
            return $cart->get('shipping_total');
        }

        if ($this->getParam('tax')) {
            return $cart->get('tax_total');
        }

        if ($this->getParam('coupon')) {
            return $cart->get('coupon_total');
        }

        return $cart->get('total');
    }

    protected function dealWithSession()
    {
        if (!Session::has(config('simple-commerce.cart_session_key'))) {
            Session::put(config('simple-commerce.cart_session_key'), Cart::make()->uuid);
        }
    }

    protected function findCart()
    {
        return Cart::find(Session::get(config('simple-commerce.cart_session_key')));
    }
}
