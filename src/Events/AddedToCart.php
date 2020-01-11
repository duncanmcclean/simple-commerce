<?php

namespace Damcclean\Commerce\Events;

use Damcclean\Commerce\Models\Cart;
use Damcclean\Commerce\Models\CartItem;
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
