<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\Cart;

use DuncanMcClean\SimpleCommerce\Http\Requests\AcceptsFormRequests;
use DuncanMcClean\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use Illuminate\Foundation\Http\FormRequest;
use Statamic\Rules\EmailWithoutPathCharacters;

class UpdateRequest extends FormRequest
{
    use AcceptsFormRequests, CartDriver;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'email' => ['nullable', 'email', new EmailWithoutPathCharacters, function ($attribute, $value, $fail) {
                if (preg_match('/^\S*$/u', $value) === 0) {
                    return $fail(__('Your email may not contain any spaces.'));
                }
            }],
            'customer.email' => ['nullable', 'email', new EmailWithoutPathCharacters, function ($attribute, $value, $fail) {
                if (preg_match('/^\S*$/u', $value) === 0) {
                    return $fail(__('Your email may not contain any spaces.'));
                }
            }],
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
