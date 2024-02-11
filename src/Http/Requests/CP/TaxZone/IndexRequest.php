<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxZone;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('view tax zones');
    }

    public function rules()
    {
        return [];
    }
}
