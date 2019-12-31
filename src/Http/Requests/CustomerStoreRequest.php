<?php

namespace Damcclean\Commerce\Http\Requests;

use Damcclean\Commerce\Facades\Customer;
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

            'billing_address_1' => 'required',
            'billing_address_2' => '',
            'billing_address_3' => '',
            'billing_city' => 'required|string',
            'billing_zip_code' => 'required|string',
            'billing_country' => 'required',
            'billing_state' => '',

            'shipping_address_1' => 'required',
            'shipping_address_2' => '',
            'shipping_address_3' => '',
            'shipping_city' => 'required|string',
            'shipping_zip_code' => 'required|string',
            'shipping_country' => 'required',
            'shipping_state' => '',
        ];
    }
}
