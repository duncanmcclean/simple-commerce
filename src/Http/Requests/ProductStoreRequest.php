<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests;

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
            'description' => '',
            'category' => '',

            'variants.*.name' => 'required|string',
            'variants.*.sku' => 'required|string',
            'variants.*.price' => ['nullable', 'required_if:free_shipping,false', 'regex:/^\d*(\.\d{2})?$/'],
            'variants.*.stock_number' => 'required',
            'variants.*.unlimited_stock' => 'required|boolean',
            'variants.*.max_quantity' => '',
            'variants.*.description' => '',
            'variants.*.attributes' => '',
        ];
    }
}
