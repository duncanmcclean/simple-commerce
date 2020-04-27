<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $gateway = $this->all()['gateway'];
        $gateway = new $gateway();

        $customerModel = config('simple-commerce.customers.model');
        $customerModel = new $customerModel();

        return array_merge($gateway->rules(), $customerModel->rules(), [
            'gateway'                           => 'required|string',
            'redirect'                          => 'nullable|string',
        ]);
    }
}
