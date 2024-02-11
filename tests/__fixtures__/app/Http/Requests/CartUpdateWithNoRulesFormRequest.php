<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Fixtures\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartUpdateWithNoRulesFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }

    public function messages()
    {
        return [];
    }
}
