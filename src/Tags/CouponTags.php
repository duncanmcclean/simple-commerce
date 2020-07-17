<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use Illuminate\Support\Facades\Session;

class CouponTags extends SubTag
{
    use Concerns\FormBuilder;

    public function redeemed()
    {
        return Cart::find(Session::get(config('simple-commerce.cart_key')))
            ->entry()
            ->data()
            ->has('coupon');
    }

    public function redeem()
    {
        return $this->createForm(
            route('statamic.simple-commerce.coupon.store'),
            [],
            'POST'
        );
    }

    public function remove()
    {
        return $this->createForm(
            route('statamic.simple-commerce.coupon.destroy'),
            [],
            'DELETE'
        );
    }
}