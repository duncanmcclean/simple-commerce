<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests;

use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use Illuminate\Foundation\Http\FormRequest;

class CouponUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return Coupon::updateRules();
    }
}
