<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\CartItem;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'quantity' => ['sometimes', 'numeric', 'gt:0'],
        ];
    }
}
