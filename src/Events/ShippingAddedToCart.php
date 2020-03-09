<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use DoubleThreeDigital\SimpleCommerce\Models\CartShipping;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingZone;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class ShippingAddedToCart
{
    use Dispatchable, InteractsWithSockets;

    public $cart;
    public $cartShipping;
    public $shippingZone;

    public function __construct(Cart $cart, CartShipping $cartShipping, ShippingZone $shippingZone)
    {
        $this->cart = $cart;
        $this->cartShipping = $cartShipping;
        $this->shippingZone = $shippingZone;
    }
}
