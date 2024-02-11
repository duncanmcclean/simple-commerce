<?php

namespace DuncanMcClean\SimpleCommerce\Rules;

use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use Illuminate\Contracts\Validation\Rule;

class CouponExists implements Rule
{
    public function passes($attribute, $value)
    {
        $coupon = Coupon::findByCode($value);

        if (! $coupon) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return __('The coupon :value does not exist.');
    }
}
