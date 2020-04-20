<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'line_item' => 'required|string',
            'quantity'  => 'required',
            'redirect'  => 'nullable|string',
        ];
    }
}
