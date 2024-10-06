<?php

namespace DuncanMcClean\SimpleCommerce\Tags;

use DuncanMcClean\SimpleCommerce\Facades\Cart as CartFacade;
use Statamic\Tags\Tags;

class Checkout extends Tags
{
    use Concerns\FormBuilder;

    public function index()
    {
        $cart = CartFacade::current();

        return $this->createForm(route('statamic.simple-commerce.checkout'), $cart->toAugmentedArray());
    }
}
