<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\Coupon;

use DuncanMcClean\SimpleCommerce\Rules\CouponExists;
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
