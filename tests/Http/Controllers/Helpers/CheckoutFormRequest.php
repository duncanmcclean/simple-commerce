<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Helpers;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'accept_terms' => ['required', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            'accept_terms.required' => 'Please accept the terms & conditions.',
        ];
    }
}
