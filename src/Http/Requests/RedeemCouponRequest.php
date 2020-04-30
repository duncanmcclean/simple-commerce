<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests;

use DoubleThreeDigital\SimpleCommerce\Rules\ValidCoupon;
use Illuminate\Foundation\Http\FormRequest;

class CartDestroyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'coupon'    => ['required', 'string', new ValidCoupon()],
            'redirect'  => 'nullable|string',
        ];
    }
}
