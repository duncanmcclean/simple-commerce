<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\Cart;

use DoubleThreeDigital\SimpleCommerce\Support\Rules\CountryExists;
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
            'name'  => 'sometimes|string',
            'email' => 'sometimes|email',
            'shipping_country' => ['sometimes', 'filled', new CountryExists],
            'billing_country' => ['sometimes', 'filled', new CountryExists],
        ];
    }
}
