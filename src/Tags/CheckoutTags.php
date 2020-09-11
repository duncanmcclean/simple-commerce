<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Gateway;
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
            $prepare = Gateway::use($gateway['class'])->prepare(request(), $cart->entry());

            $cart->update([
                $gateway['handle'] => $prepare->data(),
            ]);

            $data = array_merge($data, $prepare->data());
        }

        return $this->createForm(
            route('statamic.simple-commerce.checkout.store'),
            $data,
            'POST'
        );
    }
}
