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
            'title'                           => 'required|string',
            'slug'                            => 'required|string',
            'categories.*'                    => 'numeric',
            'is_enabled'                      => 'boolean',
            'tax_rate_id'                     => 'required',
            'needs_shipping'                  => 'boolean',
            'variants.*.name'                 => 'required|string',
            'variants.*.sku'                  => 'required|string',
            'variants.*.price'                => 'required|numeric',
            'variants.*.stock'                => 'required|numeric',
            'variants.*.unlimited_stock'      => '',
            'variants.*.max_quantity'         => 'nullable|numeric',
            'variants.*.description'          => 'nullable|string',
            'variants.*.images'               => '',
            'variants.*.weight'               => 'nullable|numeric',
        ];
    }
}
