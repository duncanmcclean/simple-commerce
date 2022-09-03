<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\Coupon;

use DoubleThreeDigital\SimpleCommerce\Countries;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxZone;
use DoubleThreeDigital\SimpleCommerce\Regions;
use DoubleThreeDigital\SimpleCommerce\Rules\CountryExists;
use DoubleThreeDigital\SimpleCommerce\Rules\RegionExists;
use Illuminate\Foundation\Http\FormRequest;

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
                // TODO: a coupon should not already exist with this code
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
                // TODO: shouldn't be over 100 if type is a percentage
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
        ];
    }
}
