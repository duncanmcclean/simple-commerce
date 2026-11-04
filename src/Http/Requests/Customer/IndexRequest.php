<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    use AuthorizesCustomer;

    public function rules()
    {
        return [];
    }
}
