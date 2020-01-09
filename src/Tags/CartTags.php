<?php

namespace Damcclean\Commerce\Tags;

use Damcclean\Commerce\Facades\Product;
use Damcclean\Commerce\Helpers\Cart;
use Statamic\Tags\Tags;

class CartTags extends Tags
{
    protected static $handle = 'cart';

    public function __construct()
    {
        $this->cart = new Cart();

        if (! session()->get('commerce_cart_id')) {
            session()->put('commerce_cart_id', $this->cart->create());
        }

        $this->cartId = session()->get('commerce_cart_id');
    }

    public function index()
    {
        return $this->cart->get($this->cartId);
    }

    public function count()
    {
        return $this->cart->count($this->cartId);
    }

    public function total()
    {
        return $this->cart->total($this->cartId);
    }
}
