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
            'name'              => 'required|string',
            'countries.*'       => 'required|numeric',
            'rates.*.name'      => 'required|string',
            'rates.*.type'      => 'required|string|in:price-based,weight-based',
            'rates.*.minimum'   => 'required|numeric',
            'rates.*.maximum'   => 'required|numeric',
            'rates.*.rate'      => 'required|string',
            'rates.*.note'      => '',
        ];
    }
}
