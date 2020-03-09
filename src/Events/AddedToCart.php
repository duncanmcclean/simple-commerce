<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class AddedToCart
{
    use Dispatchable, InteractsWithSockets;

    public $cart;
    public $cartItem;
    public $variant;

    public function __construct(Cart $cart, CartItem $cartItem, Variant $variant)
    {
        $this->cart = $cart;
        $this->cartItem = $cartItem;
        $this->variant = $variant;
    }
}
