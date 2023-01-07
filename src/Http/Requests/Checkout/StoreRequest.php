<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\Checkout;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    use CartDriver;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'name'  => ['sometimes', 'string'],
            'name'  => ['sometimes', 'email'],
        ];

        if ($this->getCart()->grandTotal() > 0) {
            $rules['gateway'] = [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (! class_exists($value)) {
                        return $fail(__(':value is not a gateway'));
                    }

                    $isGateway = (new $value()) instanceof Gateway;

                    if (! $isGateway) {
                        return $fail(__(':value is not a gateway'));
                    }
                },
            ];
        }

        return $rules;
    }
}
