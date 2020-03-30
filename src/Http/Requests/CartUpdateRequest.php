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
            'item_id'   => 'required|string',
            'quantity'  => 'required|integer',
            'redirect'  => 'nullable|string',
        ];
    }
}
