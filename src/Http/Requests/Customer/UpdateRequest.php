<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\Customer;

use DuncanMcClean\SimpleCommerce\Http\Requests\AcceptsFormRequests;
use Illuminate\Foundation\Http\FormRequest;
use Statamic\Rules\EmailWithoutPathCharacters;

class UpdateRequest extends FormRequest
{
    use AcceptsFormRequests;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'email' => ['nullable', 'email', new EmailWithoutPathCharacters],
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
