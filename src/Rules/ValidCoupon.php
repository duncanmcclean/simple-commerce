<?php

namespace DoubleThreeDigital\SimpleCommerce\Rules;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CouponNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
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
        return __('simple-commerce::messages.validation.valid_coupon');
    }
}
