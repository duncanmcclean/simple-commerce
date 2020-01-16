<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingsUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'address_1' => 'required|string',
            'address_2' => 'nullable|string',
            'address_3' => 'nullable|string',
            'city' => 'required|string',
            'country' => '', // TODO: validate something here
            'state' => '', // TODO: validate something here
            'zip_code' => 'required|string',

            'currency' => '', // TODO: validate something here,
            'currency_position' => 'required|string|in:left,right',
            'currency_separator' => 'required|string',

            'cart_index' => 'required|string',
            'cart_store' => 'required|string',
            'cart_clear' => 'required|string',
            'checkout_show' => 'required|string',
            'checkout_store' => 'required|string',
            'checkout_redirect' => 'required|string',
            'product_index' => 'required|string',
            'product_search' => 'required|string',

            'cart_retention' => 'required|integer',
        ];
    }
}
