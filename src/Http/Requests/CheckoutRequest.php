<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $gateway = $this->all()['gateway'];
        $gateway = new $gateway();

        $customerModel = config('simple-commerce.customers.model');
        $customerModel = new $customerModel();

        return array_merge($gateway->rules(), $customerModel->rules(), [
            'shipping_address_1'                => 'required|string',
            'shipping_address_2'                => '',
            'shipping_address_3'                => '',
            'shipping_city'                     => 'required|string',
            'shipping_zip_code'                 => 'required',
            'shipping_country'                  => 'required|string',
            'shipping_state'                    => 'nullable|integer',
            'billing_address_1'                 => 'required_if:use_shipping_address_for_billing,true|string',
            'billing_address_2'                 => '',
            'billing_address_3'                 => '',
            'billing_city'                      => 'required_if:use_shipping_address_for_billing,true|string',
            'billing_zip_code'                  => 'required_if:use_shipping_address_for_billing,true',
            'billing_country'                   => 'required_if:use_shipping_address_for_billing,true|string',
            'billing_state'                     => 'nullable|integer',
            'use_shipping_address_for_billing'  => 'required|in:on,off',

            'gateway'                           => 'required|string',
            'redirect'                          => 'nullable|string',
        ]);
    }
}
