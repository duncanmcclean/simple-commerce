<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('delete coupons');
    }

    public function rules()
    {
        return [];
    }
}
