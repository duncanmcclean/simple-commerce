<?php

namespace Damcclean\Commerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RedeemCouponRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => 'required|string'
        ];
    }
}
