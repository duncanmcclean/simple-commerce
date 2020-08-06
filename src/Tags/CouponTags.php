<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\SessionCart;

class CouponTags extends SubTag
{
    use Concerns\FormBuilder,
        SessionCart;

    public function index(): array
    {
        if (! $this->hasSessionCart()) return [];

        $coupon = isset($this->getSessionCart()->data['coupon']) ? $this->getSessionCart()->data['coupon'] : null;

        if ($coupon === null) return [];

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
        if (! $this->hasSessionCart()) return false;

        return isset($this->getSessionCart()->data['coupon']);
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
