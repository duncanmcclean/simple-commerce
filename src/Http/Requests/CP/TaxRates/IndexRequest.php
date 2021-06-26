<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\TaxRates;

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
