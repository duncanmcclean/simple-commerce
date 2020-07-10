<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

class CouponTags extends SubTag
{
    use Concerns\FormBuilder;

    public function redeemed()
    {
        return false;
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