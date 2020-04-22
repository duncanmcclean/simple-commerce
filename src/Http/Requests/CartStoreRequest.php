<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'variant'   => 'required|string',
            'quantity'  => 'required',
            'redirect'  => 'nullable|string',
            'note'      => 'nullable|string',
        ];
    }
}
