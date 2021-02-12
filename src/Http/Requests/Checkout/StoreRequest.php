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
        // TODO: Need to validate we have stock left of all products before continuing

        $rules = [
            'name' => 'sometimes|string',
            'email' => 'sometimes|email',
        ];

        if ($this->getCart()->get('grand_total') > 0) {
            dd($this->getCart());

            $rules['gateway'] = ['required', 'string', new IsAGateway];
        }

        return $rules;
    }
}
