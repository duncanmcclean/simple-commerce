<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CP\Coupon;

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
            'customer_eligibility' => [
                'nullable',
                'string',
                Rule::in(['all', 'specific_customers', 'customers_by_domain']),
            ],
            'customers' => [
                'required_if:customer_eligibility,specific_customers',
                'array',
            ],
            'customers_by_domain' => [
                'required_if:customer_eligibility,customers_by_domain',
                'array',
            ],
            'customers_by_domain.*' => [
                'required',
                'string',
                'regex:/^[^@]*$/',
            ],
            'valid_from' => [
                'nullable',
                'array',
            ],
            'valid_from.date' => [
                'nullable',
                'date',
            ],
            'valid_from.time' => [
                'nullable',
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
        ];
    }
}
