<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Facades\Session;

class CheckoutTags extends SubTag
{
    use Concerns\FormBuilder;

    public function index()
    {
        $cart = Cart::find(Session::get(config('simple-commerce.cart_key')));
        $data = $cart->data;

        if (! isset($data['is_paid']) || $data['is_paid'] === false) {
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
