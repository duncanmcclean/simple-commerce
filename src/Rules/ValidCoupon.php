<?php

namespace DuncanMcClean\SimpleCommerce\Rules;

use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Exceptions\CouponNotFound;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use Illuminate\Contracts\Validation\Rule;

class ValidCoupon implements Rule
{
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function passes($attribute, $value)
    {
        try {
            return Coupon::findByCode($value)->isValid($this->order);
        } catch (CouponNotFound $e) {
            return false;
        }
    }

    public function message()
    {
        return __('Sorry, this coupon is not valid for your order.');
    }
}
