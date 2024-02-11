<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\Customer;

use DuncanMcClean\SimpleCommerce\Http\Requests\AcceptsFormRequests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    use AcceptsFormRequests;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        if ($formRequest = $this->get('_request')) {
            return $this->buildFormRequest($formRequest, $this)->rules();
        }

        return [];
    }

    public function messages()
    {
        if ($formRequest = $this->get('_request')) {
            return $this->buildFormRequest($formRequest, $this)->messages();
        }

        return [];
    }
}
