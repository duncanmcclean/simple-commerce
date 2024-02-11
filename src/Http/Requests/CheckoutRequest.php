<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests;

use DuncanMcClean\SimpleCommerce\Facades\Gateway;
use DuncanMcClean\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use DuncanMcClean\SimpleCommerce\Rules\ValidCoupon;
use DuncanMcClean\SimpleCommerce\Rules\ValidGateway;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    use AcceptsFormRequests, CartDriver;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['sometimes', 'string'],
            'email' => ['nullable', 'email', function ($attribute, $value, $fail) {
                if (preg_match('/^\S*$/u', $value) === 0) {
                    return $fail(__('Your email may not contain any spaces.'));
                }
            }],
            'customer.email' => ['nullable', 'email', function ($attribute, $value, $fail) {
                if (preg_match('/^\S*$/u', $value) === 0) {
                    return $fail(__('Your email may not contain any spaces.'));
                }
            }],
            'coupon' => ['nullable', new ValidCoupon($this->getCart())],
        ];

        if ($request = $this->get('_request')) {
            $rules = array_merge($rules, $this->buildFormRequest($request, $this)->rules());
        }

        if ($this->getCart()->grandTotal() > 0) {
            $rules['gateway'] = ['required', 'string', new ValidGateway];
        }

        if ($this->shouldDoGatewayValidation()) {
            $rules = array_merge($rules, Gateway::use($this->get('gateway'))->checkoutRules());
        }

        return $rules;
    }

    public function messages(): array
    {
        return array_merge(
            $this->get('_request')
                ? $this->buildFormRequest($this->get('_request'), $this)->messages()
                : [],
            $this->shouldDoGatewayValidation()
                ? Gateway::use($this->get('gateway'))->checkoutMessages()
                : [],
        );
    }

    protected function shouldDoGatewayValidation(): bool
    {
        return SimpleCommerce::gateways()->where('handle', $this->get('gateway'))->count() > 0;
    }
}
