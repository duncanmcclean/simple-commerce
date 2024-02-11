<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CP\Coupon;

use Illuminate\Foundation\Http\FormRequest;

class EditRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('edit coupons');
    }

    public function rules()
    {
        return [];
    }
}
