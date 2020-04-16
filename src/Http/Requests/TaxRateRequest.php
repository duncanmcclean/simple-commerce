<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaxRateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'              => 'required|string',
            'rate'              => 'required|string',
            'description'       => 'nullable|string',
        ];
    }
}
