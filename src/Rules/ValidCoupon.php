<?php

namespace DoubleThreeDigital\SimpleCommerce\Rules;

use DoubleThreeDigital\SimpleCommerce\Models\Coupon;
use Illuminate\Contracts\Validation\Rule;

class ValidCoupon implements Rule
{
    public function passes($attribute, $value)
    {
        $coupon = Coupon::where('code', $value)->first();

        if (!$coupon) {
            return false;
        }

        return $coupon->isActive();
    }

    public function message()
    {
        return 'The coupon provided does not exist or is not valid.';
    }
}
