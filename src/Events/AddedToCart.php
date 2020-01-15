<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class AddedToCart
{
    use Dispatchable, InteractsWithSockets;

    public $cart;
    public $cartItem;

    public function __construct(Cart $cart, CartItem $cartItem)
    {
        $this->cart = $cart;
        $this->cartItem = $cartItem;
    }
}
