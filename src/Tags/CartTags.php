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
    }

    public function index()
    {
        return $this->cart->all();
    }

    public function count()
    {
        return $this->cart->count();
    }

    public function total()
    {
        return $this->cart->total();
    }
}
