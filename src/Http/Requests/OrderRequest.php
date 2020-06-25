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
            'item_total'            => 'required',
            'tax_total'             => 'required',
            'shipping_total'        => 'required',
            'total'                 => 'required',
            'line_items'            => 'required',

            'billing_uuid'          => 'required|string',
            'billing_address1'      => 'required|string',
            'billing_address2'      => '',
            'billing_address3'      => '',
            'billing_city'          => 'required|string',
            'billing_zip_code'      => 'required',
            'billing_country'       => 'required|integer',
            'billing_state'         => 'nullable|integer',

            'shipping_uuid'         => 'required|string',
            'shipping_address1'     => 'required|string',
            'shipping_address2'     => '',
            'shipping_address3'     => '',
            'shipping_city'         => 'required|string',
            'shipping_zip_code'     => 'required',
            'shipping_country'      => 'required|integer',
            'shipping_state'        => 'nullable|integer',

            'customer_id'           => 'required|string',
            'order_status_id'       => 'required|string',
            'currency_id'           => 'required|string',
            'is_paid'               => 'required|in:true,false',
            'is_completed'          => 'required|in:true,false',
        ];
    }
}
