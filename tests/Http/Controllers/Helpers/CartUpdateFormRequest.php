<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Helpers;

use Illuminate\Foundation\Http\FormRequest;

class CartUpdateFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'shipping_special' => ['required', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            'shipping_special.required' => 'Coolzies. An error message.',
        ];
    }
}
