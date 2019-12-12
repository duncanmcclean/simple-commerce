<?php

namespace Damcclean\Commerce\Http\Requests;

use Damcclean\Commerce\Facades\Customer;
use Illuminate\Foundation\Http\FormRequest;

class CustomerStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return Customer::updateRules();
    }
}
