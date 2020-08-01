<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\Coupon;

use DoubleThreeDigital\SimpleCommerce\Rules\EntryExists;
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
            'coupon' => ['required', 'string', new EntryExists],
        ];
    }
}