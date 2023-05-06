<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Helpers;

use Illuminate\Foundation\Http\FormRequest;

class CartItemStoreFormRequest extends FormRequest
{
    public function rules()
    {
        return [
            'smth' => ['required', 'string'],
        ];
    }
}
