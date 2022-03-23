<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\Coupon;

use DoubleThreeDigital\SimpleCommerce\Rules\CouponExists;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => ['required', 'string', new CouponExists()],
        ];
    }
}
