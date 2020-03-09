<?php

namespace DoubleThreeDigital\SimpleCommerce\Http;

use DoubleThreeDigital\SimpleCommerce\Helpers\Cart;

trait UsesCart
{
    public $cart;
    public $cartId;

    public function __construct()
    {
        $this->cart = new Cart();
    }

    public function createCart()
    {
        if (! request()->session()->get('commerce_cart_id')) {
            request()->session()->put('commerce_cart_id', $this->cart->create());
            request()->session()->save();
        }

        $this->cartId = request()->session()->get('commerce_cart_id');
    }

    public function replaceCart()
    {
        $this->createCart();

        $this->cart->clear($this->cartId);

        request()->session()->remove('commerce_cart_id');

        $this->createCart();
    }
}
