<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxRate;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create tax rates');
    }

    public function rules()
    {
        return [
            'taxCategory' => ['required', 'string'],
        ];
    }
}
