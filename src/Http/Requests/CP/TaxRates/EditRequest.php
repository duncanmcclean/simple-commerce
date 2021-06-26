<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\TaxRates;

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
