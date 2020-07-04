<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags\Concerns;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Facades\Session;

trait CheckoutTags
{
    public function checkout()
    {
        $data = [];
        $cartData = Cart::find(Session::get('simple-commerce-cart'))->entry()->data()->toArray();

        foreach (SimpleCommerce::gateways() as $gateway) {
            $class = new $gateway['class']();

            $data = array_merge($data, $class->prepare($cartData));
        }

        return $this->createForm(
            route('statamic.simple-commerce.checkout.store'),
            $data,
            'POST'
        );
    }
}