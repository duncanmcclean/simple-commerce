<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxZone;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('delete tax zones');
    }

    public function rules()
    {
        return [];
    }
}
