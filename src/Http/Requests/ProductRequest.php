<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string',
            'description' => 'nullable|string',

            'variants.*.name' => 'required|string',
            'variants.*.price' => 'required',
            'variants.*.sku' => 'required|string',
            'variants.*.max_quantity' => 'required|integer',
            'variants.*.stock' => 'required|integer',
            'variants.*.unlimited_stock' => 'required|boolean',
            'variants.*.description' => 'nullable|string',
            'variants.*.variant_attributes.*.uuid' => 'nullable|string',
            'variants.*.variant_attributes.*.key' => 'required|string',
            'variants.*.variant_attributes.*.value' => 'required|string',

            'product_attributes.*.uuid' => 'nullable|string',
            'product_attributes.*.key' => 'required|string',
            'product_attributes.*.value' => 'required|string',

            'slug' => 'required|string',
            'category.*' => 'required|integer',
        ];
    }
}
