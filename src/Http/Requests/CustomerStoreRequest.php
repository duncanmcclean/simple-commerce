<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests;

use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use Illuminate\Foundation\Http\FormRequest;

class CustomerStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email',

            'billing_address_1' => 'nullable|string',
            'billing_address_2' => 'nullable|string',
            'billing_address_3' => 'nullable|string',
            'billing_city' => 'nullable|string',
            'billing_zip_code' => 'nullable|string',
            'billing_country' => 'required_with:billing_address_1',
            'billing_state' => '',

            'shipping_address_1' => 'nullable|string',
            'shipping_address_2' => 'nullable|string',
            'shipping_address_3' => 'nullable|string',
            'shipping_city' => 'nullable|string',
            'shipping_zip_code' => 'nullable|string',
            'shipping_country' => 'required_with:shipping_address_1',
            'shipping_state' => '',
        ];
    }
}
