<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
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

        return Cart::find(Session::get(config('simple-commerce.cart_session_key')))->get('line_items');
    }

    public function count()
    {
        $this->dealWithSession();

        return Cart::find(Session::get(config('simple-commerce.cart_session_key')))->get('items_count');
    }

    public function total()
    {
        if ($this->getParam('items')) {
            return Currency::parse(Cart::find(Session::get(config('simple-commerce.cart_session_key')))->get('item_total'));
        }

        if ($this->getParam('shipping')) {
            return Currency::parse(Cart::find(Session::get(config('simple-commerce.cart_session_key')))->get('shipping_total'));
        }

        if ($this->getParam('tax')) {
            return Currency::parse(Cart::find(Session::get(config('simple-commerce.cart_session_key')))->get('tax_total'));
        }

        if ($this->getParam('coupon')) {
            return Currency::parse(Cart::find(Session::get(config('simple-commerce.cart_session_key')))->get('coupon_total'));
        }

        return Currency::parse(Cart::find(Session::get(config('simple-commerce.cart_session_key')))->get('total'));
    }

    protected function dealWithSession()
    {
        if (! Session::has(config('simple-commerce.cart_session_key'))) {
            Session::put(config('simple-commerce.cart_session_key'), Cart::make()->uuid);
        }
    }
}
