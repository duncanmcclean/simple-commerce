<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'line_item'                         => 'nullable|string',
            'quantity'                          => 'nullable|numeric',

            'shipping_name'                     => 'nullable|string',
            'shipping_address_1'                => 'nullable|string',
            'shipping_address_2'                => 'nullable|string',
            'shipping_address_3'                => 'nullable|string',
            'shipping_city'                     => 'nullable|string',
            'shipping_zip_code'                 => 'nullable|string',
            'shipping_country'                  => 'nullable|string',
            'shipping_state'                    => 'nullable|integer',
            'shipping_name'                     => 'required_if:use_shipping_address_for_billing,true|string',
            'billing_address_1'                 => 'required_if:use_shipping_address_for_billing,true|string',
            'billing_address_2'                 => 'nullable|string',
            'billing_address_3'                 => 'nullable|string',
            'billing_city'                      => 'required_if:use_shipping_address_for_billing,true|string',
            'billing_zip_code'                  => 'required_if:use_shipping_address_for_billing,true',
            'billing_country'                   => 'required_if:use_shipping_address_for_billing,true|string',
            'billing_state'                     => 'nullable|integer',
            'use_shipping_address_for_billing'  => 'nullable|in:on,off',

            'redirect'                          => 'nullable|string',
        ];
    }
}
