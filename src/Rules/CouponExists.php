<?php

namespace DoubleThreeDigital\SimpleCommerce\Rules;

use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use Illuminate\Contracts\Validation\Rule;

class CouponExists implements Rule
{
    public function passes($attribute, $value)
    {
        return Coupon::findByCode($value) === null ? false : true;
    }

    public function message()
    {
        return __('simple-commerce::validation.entry_exists');
    }
}
