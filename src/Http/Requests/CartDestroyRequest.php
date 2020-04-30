<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartDestroyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clear'     => 'required_if:item_id,null|in:true',
            'item_id'   => 'required_if:clear,null|string',
            '_redirect' => 'nullable|string',
        ];
    }
}
