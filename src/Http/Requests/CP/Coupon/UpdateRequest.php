<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('edit coupons');
    }

    public function rules()
    {
        return [
            'code' => [
                'required',
                'string',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'type' => [
                'required',
                'string',
                'in:fixed,percentage',
            ],
            'value' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($this->type === 'percentage' && $value > 100) {
                        $fail(__('Percentage value cannot be over 100.'));
                    }
                },
            ],
            'maximum_uses' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'minimum_cart_value' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'products' => [
                'nullable',
                'array',
            ],
            'customers' => [
                'nullable',
                'array',
            ],
            'expires_at' => [
                'nullable',
                'date',
            ],
            'enabled' => [
                'required',
                'boolean',
            ],
        ];
    }
}
