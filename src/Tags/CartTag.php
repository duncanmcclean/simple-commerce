<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use Statamic\Tags\Tags;

class CartTag extends Tags
{
    protected static $handle = 'cart';

    public function index()
    {
        return;
    }

    public function items()
    {
        return;
    }

    public function shipping()
    {
        return;
    }

    public function tax()
    {
        return;
    }

    public function count()
    {
        return Currency::parse(2.50, true, true);
    }

    public function total()
    {
        //

        if ($this->getParam('items')) {
            //
        }

        if ($this->getParam('shipping')) {
            //
        }

        if ($this->getParam('tax')) {
            //
        }

        return;
    }
}
