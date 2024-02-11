<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CP\Coupon;

use DuncanMcClean\SimpleCommerce\Facades\Coupon;
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
