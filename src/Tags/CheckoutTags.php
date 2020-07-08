<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class CheckoutTags extends SubTag
{
    use Concerns\FormBuilder;

    public function index()
    {
        $data = [];
        $cartData = Cart::find(Session::get(config('simple-commerce.cart_key')))
            ->entry()
            ->data()
            ->toArray();

        foreach (SimpleCommerce::gateways() as $gateway) {
            $class = new $gateway['class']();

            $data = array_merge($data, $class->prepare($cartData));
        }

        if (isset($data['is_paid'])) {
            $data['is_paid'] = $cartData['is_paid'];
        }

        if ($cartData['is_paid'] === true) {
            $data['receipt_url'] = URL::temporarySignedRoute('statamic.simple-commerce.receipt.show', now()->addHour(), [
                'orderId' => Session::get(config('simple-commerce.cart_key')),
            ]);
        }

        return $this->createForm(
            route('statamic.simple-commerce.checkout.store'),
            $data,
            'POST'
        );
    }
}