<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CP\Coupon;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create coupons');
    }

    public function rules()
    {
        return [];
    }
}
