<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'total' => 'required|string',
            'notes' => 'nullable|string',
            'items' => 'required',
            'order_status_id' => 'required|integer',
            'currency_id' => 'required|integer',
            'customer_id' => 'required|integer',

            'billing_address_1' => 'required|string',
            'billing_address_2' => '',
            'billing_address_3' => '',
            'billing_city' => 'required|string',
            'billing_zip_code' => 'required',
            'billing_country' => 'required|integer',
            'billing_state' => 'nullable|integer',

            'shipping_address_1' => 'required|string',
            'shipping_address_2' => '',
            'shipping_address_3' => '',
            'shipping_city' => 'required|string',
            'shipping_zip_code' => 'required',
            'shipping_country' => 'required|integer',
            'shipping_state' => 'nullable|integer',
        ];
    }
}
