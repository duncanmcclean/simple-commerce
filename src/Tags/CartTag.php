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

        return Cart::find(Session::get('simple_commerce_cart'))->get('lineItems');
    }

    public function count()
    {
        return $this->items()->count();
    }

    public function total()
    {
        if ($this->getParam('items')) {
            return Cart::find(Session::get('simple_commerce_cart'))->item_total;
        }

        if ($this->getParam('shipping')) {
            return Cart::find(Session::get('simple_commerce_cart'))->shipping_total;
        }

        if ($this->getParam('tax')) {
            return Cart::find(Session::get('simple_commerce_cart'))->tax_total;
        }

        return Cart::find(Session::get('simple_commerce_cart'))->total;
    }

    protected function dealWithSession()
    {
        if (! Session::has('simple_commerce_cart')) {
            Session::put('simple_commerce_cart', Cart::make()->id);
        }
    }
}
