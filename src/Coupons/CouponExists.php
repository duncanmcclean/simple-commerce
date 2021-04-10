<?php

namespace DoubleThreeDigital\SimpleCommerce\Coupons;

use DoubleThreeDigital\SimpleCommerce\Exceptions\CouponNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use Illuminate\Contracts\Validation\Rule;

class CouponExists implements Rule
{
    public function passes($attribute, $value)
    {
        try {
            return Coupon::findByCode($value) === null ? false : true;
        } catch (CouponNotFound $e) {
            return false;
        }
    }

    public function message()
    {
        return __('simple-commerce::messages.entry_exists');
    }
}
