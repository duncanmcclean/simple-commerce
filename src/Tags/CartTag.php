<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Helpers\Currency;
use DoubleThreeDigital\SimpleCommerce\Http\UsesCart;
use Statamic\Tags\Tags;

class CartTag extends Tags
{
    use UsesCart;

    protected static $handle = 'cart';

    public function __construct()
    {
        $this->createCart();
    }

    public function index()
    {
        return $this->cart()->get($this->cartId)->toArray();
    }

    public function items()
    {
        return $this->index();
    }

    public function shipping()
    {
        return $this->cart()->getShipping($this->cartId);
    }

    public function tax()
    {
        return $this->cart()->getTax($this->cartId);
    }

    public function count()
    {
        return $this->cart()->count($this->cartId);
    }

    public function total()
    {
        $total = $this->cart()->total($this->cartId);

        if ($this->getParam('items')) {
            $total = $this->cart()->total($this->cartId, 'items');
        }

        if ($this->getParam('shipping')) {
            $total = $this->cart()->total($this->cartId, 'shipping');
        }

        if ($this->getParam('tax')) {
            $total = $this->cart()->total($this->cartId, 'tax');
        }

        return (new Currency())->parse($total, true, true);
    }
}
