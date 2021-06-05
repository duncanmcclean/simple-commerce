<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\Coupon;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\HasValidFormParameters;
use DoubleThreeDigital\SimpleCommerce\Support\Rules\CouponExists;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    use HasValidFormParameters;

    public function authorize()
    {
        return $this->hasValidFormParameters();
    }

    public function rules()
    {
        return [
            'code' => ['required', 'string', new CouponExists()],
        ];
    }
}
