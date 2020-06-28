<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags\Concerns;

trait CouponTags
{
    public function redeemCoupon()
    {
        return $this->createForm(
            route('statamic.simple-commerce.coupon.store'),
            [],
            'POST'
        );
    }

    public function removeCoupon()
    {
        return $this->createForm(
            route('statamic.simple-commerce.coupon.destroy'),
            [],
            'DELETE'
        );
    }
}