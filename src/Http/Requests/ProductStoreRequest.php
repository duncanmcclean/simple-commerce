<?php

namespace Damcclean\Commerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string',
            'slug' => 'required|string',
            'publish_date' => '',
            'expiry_date' => '',
            'enabled' => 'boolean',
            'free_shipping' => 'boolean',
            'shipping_price' => ['sometimes', 'regex:/^\d*(\.\d{2})?$/'],
            'price' => ['sometimes', 'regex:/^\d*(\.\d{2})?$/'],
            'stock_number' => 'sometimes|integer'
        ];
    }
}
