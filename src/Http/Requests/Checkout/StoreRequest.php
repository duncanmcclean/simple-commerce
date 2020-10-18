<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\Checkout;

use DoubleThreeDigital\SimpleCommerce\Rules\IsAGateway;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // TODO: Need to validate we have stock left of all products before continuing

        return [
            'name' => 'sometimes|string',
            'email' => 'sometimes|email',
            'gateway' => ['required', 'string', new IsAGateway],
        ];
    }
}
