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

        if (!isset($data['is_paid']) || $data['is_paid'] === false) {
            foreach (SimpleCommerce::gateways() as $gateway) {
                $class = new $gateway['class']();

                $data = array_merge($data, $class->prepare($data));
            }
        }

        return $this->createForm(
            route('statamic.simple-commerce.checkout.store'),
            $data,
            'POST'
        );
    }
}
