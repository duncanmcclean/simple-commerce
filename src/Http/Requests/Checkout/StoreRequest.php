<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\Checkout;

use DoubleThreeDigital\SimpleCommerce\Gateways\Rules\IsAGateway;
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
            'name'  => 'sometimes|string',
            'email' => 'sometimes|email',
        ];

        if ($this->getCart()->grandTotal() > 0) {
            $rules['gateway'] = ['required', 'string', new IsAGateway()];
        }

        return $rules;
    }
}
