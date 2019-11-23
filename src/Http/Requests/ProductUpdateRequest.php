<?php

namespace Damcclean\Commerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
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
            'price' => 'required|integer',
            'shipping_price' => 'sometimes|integer',
            'stock_number' => 'sometimes|integer'
        ];
    }
}
