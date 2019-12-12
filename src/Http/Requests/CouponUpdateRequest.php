<?php

namespace Damcclean\Commerce\Http\Requests;

use Damcclean\Commerce\Facades\Coupon;
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
