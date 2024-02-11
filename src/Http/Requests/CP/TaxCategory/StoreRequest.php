<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxCategory;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create tax categories');
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ];
    }
}
