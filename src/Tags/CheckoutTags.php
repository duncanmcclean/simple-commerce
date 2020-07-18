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
        $data = Cart::find(Session::get(config('simple-commerce.cart_key')))
            ->entry()
            ->data()
            ->toArray();

        if (! isset($data['is_paid']) || $data['is_paid'] === false) {
            foreach (SimpleCommerce::gateways() as $gateway) {
                $class = new $gateway['class']();
    
                $data = array_merge($data, $class->prepare($data));
            }
        }

        if ($data['is_paid'] === true) {
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