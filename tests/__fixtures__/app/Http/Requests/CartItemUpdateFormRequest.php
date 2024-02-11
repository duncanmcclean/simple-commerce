<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Fixtures\Http\Requests;

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
