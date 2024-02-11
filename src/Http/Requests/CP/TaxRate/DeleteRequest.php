<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxRate;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('delete tax rates');
    }

    public function rules()
    {
        return [];
    }
}
