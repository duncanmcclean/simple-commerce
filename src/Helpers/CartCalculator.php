<?php

namespace DoubleThreeDigital\SimpleCommerce\Helpers;

use DoubleThreeDigital\SimpleCommerce\Models\Cart as CartModel;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;

class CartCalculator
{
    public $cart;
    public $items;

    public function __construct(CartModel $cart)
    {
        $this->cart = $cart;
        $this->items = CartItem::where('cart_id', $cart->id);
    }

    public function calculate()
    {
        return 0;
    }
}
