<?php

namespace Damcclean\Commerce\Http\Requests;

use Damcclean\Commerce\Facades\Order;
use Illuminate\Foundation\Http\FormRequest;

class OrderUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'total' => 'required',
            'notes' => '',

            'products.*.product' => '',
            'products.*.quantity' => '',

            'shipping_address_1' => 'required|string',
            'shipping_address_2' => '',
            'shipping_address_3' => '',
            'shipping_city' => 'required|string',
            'shipping_zip_code' => 'required',
            'shipping_country' => 'required',
            'shipping_state' => '',

            'status' => 'required',
            'customer' => 'required',
            'order_date' => 'required'
        ];
    }
}
