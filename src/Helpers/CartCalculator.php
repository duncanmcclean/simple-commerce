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
        $this
            ->itemsTotal()
            ->shippingTotal()
            ->taxTotal();

        return $this->total;
    }

    public function itemsTotal()
    {
        collect($this->items)
            ->each(function ($item) {
                $this->add($item['variant']->price * $item['quantity']);
            });

        return $this;
    }

    public function shippingTotal()
    {
        collect($this->shipping)
            ->each(function ($item) {
                $this->add($item['shippingZone']->price);
            });

        return $this;
    }

    public function taxTotal()
    {
        if (config('simple-commerce.entered_with_tax')) {
            return $this;
        }

        // TODO: this stuff doesn't actually work for some reason

        collect($this->tax)
            ->each(function ($item) {
                $this->add("0.{$item['taxRate']->rate}" * $this->total);
            });

        return $this;
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
