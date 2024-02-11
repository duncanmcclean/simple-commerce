<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CP\Coupon;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('view coupons');
    }

    public function rules()
    {
        return [];
    }
}
