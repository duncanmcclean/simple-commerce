<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxCategory;

use Illuminate\Foundation\Http\FormRequest;

class EditRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('edit tax categories');
    }

    public function rules()
    {
        return [];
    }
}
