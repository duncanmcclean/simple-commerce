<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxCategory;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create tax categories');
    }

    public function rules()
    {
        return [];
    }
}
