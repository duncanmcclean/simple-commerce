<?php

namespace DoubleThreeDigital\SimpleCommerce\Helpers;

use DoubleThreeDigital\SimpleCommerce\Models\Cart as CartModel;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;
use DoubleThreeDigital\SimpleCommerce\Models\CartShipping;
use DoubleThreeDigital\SimpleCommerce\Models\CartTax;

class CartCalculator
{
    public $cart;
    public $items;
    public $shipping;
    public $tax;
    public $total = 0;

    public function __construct(CartModel $cart)
    {
        $this->cart = $cart;

        $this->items = CartItem::with('product', 'variant')
            ->where('cart_id', $cart->id)
            ->get();

        $this->shipping = CartShipping::with('shippingZone')
            ->where('cart_id', $cart->id)
            ->get();

        $this->tax = CartTax::with('taxRate')
            ->where('cart_id', $cart->id)
            ->get();
    }

    public function calculate()
    {
        collect($this->items)
            ->each(function ($item) {
                $this->add($item['variant']->price * $item['quantity']);
            });

        collect($this->shipping)
            ->each(function ($item) {
                $this->add($item['shippingZone']->rate);
            });

        collect($this->tax)
            ->each(function ($item) {
                $this->add(($item['taxRate']->rate / 100) * $this->total);
            });

        return $this->total;
    }

    protected function subtract(int $number)
    {
        $this->total -= $number;
    }

    protected function add(int $number)
    {
        $this->total += $number;
    }
}
