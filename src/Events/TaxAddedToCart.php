<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use DoubleThreeDigital\SimpleCommerce\Models\CartTax;
use DoubleThreeDigital\SimpleCommerce\Models\TaxRate;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class TaxAddedToCart
{
    use Dispatchable, InteractsWithSockets;

    public $cart;
    public $cartTax;
    public $taxRate;

    public function __construct(Cart $cart, CartTax $cartTax, TaxRate, $taxRate)
    {
        $this->cart = $cart;
        $this->cartTax = $cartTax;
        $this->taxRate = $taxRate;
    }
}
