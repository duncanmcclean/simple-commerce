<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxCategory;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('delete tax categories');
    }

    public function rules()
    {
        return [];
    }
}
