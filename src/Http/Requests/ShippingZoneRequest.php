<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShippingZoneRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'country.*' => 'required|integer',
            'state.*' => 'nullable|integer',
            'start_of_zip_code' => 'nullable|string',
            'price' => 'required|string',
        ];
    }
}
