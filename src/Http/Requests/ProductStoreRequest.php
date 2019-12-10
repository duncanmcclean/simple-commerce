<?php

namespace Damcclean\Commerce\Http\Requests;

use Damcclean\Commerce\Facades\Product;
use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return Product::createRules();
    }
}
