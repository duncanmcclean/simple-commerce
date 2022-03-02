<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\CartItem;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\AcceptsFormRequests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    use AcceptsFormRequests;

    protected $errorBag = 'simple-commerce';

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'quantity' => ['sometimes', 'numeric', 'gt:0'],
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
