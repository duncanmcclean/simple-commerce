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
            'country.*'         => 'required|integer',
            'state.*'           => 'nullable|integer',
            'start_of_zip_code' => 'nullable|string',
            'rate'              => 'required|string',
        ];
    }
}
