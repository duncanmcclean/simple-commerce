<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\TaxRates;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create tax rates');
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string'],
            'rate' => ['required', 'numeric'],
            'category' => ['required', 'string'], // TODO
            'zone' => ['required', 'string'], // TODO
        ];
    }
}
