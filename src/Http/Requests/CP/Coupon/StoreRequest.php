<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon;

use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create coupons');
    }

    public function rules()
    {
        return [
            'code' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (Coupon::findByCode($value)) {
                        $fail(__('A coupon with this code already exists.'));
                    }
                },
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
                        $fail(__('For percentage coupons, the value can not be over 100.'));
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
                'array',
            ],
            'expires_at.date' => [
                'nullable',
                'date',
            ],
            'expires_at.time' => [
                'nullable',
            ],
            'enabled' => [
                'required',
                'boolean',
            ],
        ];
    }
}
