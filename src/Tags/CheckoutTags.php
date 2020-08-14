<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\SessionCart;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;

class CheckoutTags extends SubTag
{
    use Concerns\FormBuilder,
        SessionCart;

    public function index()
    {
        $cart = $this->getSessionCart();
        $data = $cart->data;

        foreach (SimpleCommerce::gateways() as $gateway) {
            $class = new $gateway['class']();
            $prepare = $class->prepare($cart->data);

            $cart->update([
                $gateway['handle'] => $prepare,
            ]);

            $data = array_merge($data, $prepare);
        }

        return $this->createForm(
            route('statamic.simple-commerce.checkout.store'),
            $data,
            'POST'
        );
    }
}
