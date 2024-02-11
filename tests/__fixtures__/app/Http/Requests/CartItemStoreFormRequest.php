<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Fixtures\Http\Requests;

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
