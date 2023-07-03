<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
                Rule::in(['fixed', 'percentage']),
            ],
            'value' => [
                'required',
                'array',
            ],
            'value.mode' => [
                'required',
                'string',
                Rule::in(['fixed', 'percentage']),
            ],
            'value.value' => [
                'required',
                'min:0',
                'numeric',
                function ($attribute, $value, $fail) {
                    if ($this->type === 'percentage' && $value > 100) {
                        $fail(__("For percentage coupons, the value can not be over 100."));
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
