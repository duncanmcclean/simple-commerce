<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;

class CouponTags extends SubTag
{
    use Concerns\FormBuilder;
    use CartDriver;

    public function index(): array
    {
        if (!$this->hasCart()) {
            return [];
        }

        $coupon = isset($this->getCart()->data['coupon'])
            ? $this->getCart()->data['coupon']
            : null;

        if ($coupon === null) {
            return [];
        }

        $coupon = Coupon::find($coupon);

        return $coupon->toAugmentedArray();
    }

    public function has()
    {
        if (! $this->hasCart()) {
            return false;
        }

        return ! is_null($this->getCart()->coupon());
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
