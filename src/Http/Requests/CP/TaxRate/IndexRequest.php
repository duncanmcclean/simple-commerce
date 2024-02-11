<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxRate;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('view tax rates');
    }

    public function rules()
    {
        return [];
    }
}
