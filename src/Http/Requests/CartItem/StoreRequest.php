<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\CartItem;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\AcceptsFormRequests;
use DoubleThreeDigital\SimpleCommerce\Support\Rules\EntryExists;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    use AcceptsFormRequests;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'product'  => ['required', 'string', new EntryExists()],
            'variant'  => ['string'],
            'quantity' => ['required', 'numeric', 'gt:0'],
        ];

        if ($formRequest = $this->get('_request')) {
            return array_merge(
                $rules,
                $this->buildFormRequest($formRequest, $this)->rules()
            );
        }

        return $rules;
    }

    public function messages()
    {
        if ($formRequest = $this->get('_request')) {
            return $this->buildFormRequest($formRequest, $this)->messages();
        }

        return [];
    }
}
