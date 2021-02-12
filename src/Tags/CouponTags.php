<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;

class CouponTags extends SubTag
{
    use Concerns\FormBuilder,
        CartDriver;

    public function index(): array
    {
        if (! $this->hasCart()) {
            return [];
        }

        $coupon = isset($this->getCart()->data['coupon'])
            ? $this->getCart()->data['coupon']
            : null;

        if ($coupon === null) {
            return [];
        }

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
        if (! $this->hasCart()) {
            return false;
        }

        return isset($this->getCart()->data['coupon']);
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
