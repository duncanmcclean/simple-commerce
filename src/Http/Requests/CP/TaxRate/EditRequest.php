<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxRate;

use Illuminate\Foundation\Http\FormRequest;

class EditRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('edit tax rates');
    }

    public function rules()
    {
        return [];
    }
}
