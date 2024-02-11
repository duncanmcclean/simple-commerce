<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxZone;

use Illuminate\Foundation\Http\FormRequest;

class EditRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('edit tax zones');
    }

    public function rules()
    {
        return [];
    }
}
