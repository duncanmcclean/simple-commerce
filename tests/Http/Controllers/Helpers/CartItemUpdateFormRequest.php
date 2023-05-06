<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Helpers;

use Illuminate\Foundation\Http\FormRequest;

class CartItemUpdateFormRequest extends FormRequest
{
    public function rules()
    {
        return [
            'coolzies' => ['required', 'string'],
        ];
    }
}
