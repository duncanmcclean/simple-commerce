<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'          => 'required|string',
            'code'          => 'required|string',
            'type'          => 'required|in:percent_discount,fixed_discount,free_shipping',
            'value'         => 'nullable|numeric',
            'minimum_total' => 'nullable|numeric',
            'total_uses'    => 'nullable|numeric',
            'start_date'    => '',
            'end_date'      => '',
        ];
    }
}
