<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxZone;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create tax zones');
    }

    public function rules()
    {
        return [];
    }
}
