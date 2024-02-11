<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Fixtures\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerUpdateFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'business_name' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'business_name.required' => "You can't have a business without a name. Silly sausage!",
        ];
    }
}
