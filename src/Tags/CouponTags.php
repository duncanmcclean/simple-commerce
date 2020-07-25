<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use Illuminate\Support\Facades\Session;

class CouponTags extends SubTag
{
    use Concerns\FormBuilder;

    public function index()
    {
        $coupon = Cart::find(Session::get(config('simple-commerce.cart_key')))
            ->entry()
            ->data()
            ->get('coupon');

        // TODO: ideally, here we'd use an augmented array from Statamic but it wasn't working when trying to implement it

        $coupon = Coupon::find($coupon);

        return array_merge($coupon->data, [
            'title' => $coupon->entry()->title,
            'slug'  => $coupon->entry()->slug(),
            'id'    => $coupon->id,
        ]);
    }

    public function has()
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
