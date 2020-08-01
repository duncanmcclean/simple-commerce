<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\CartItem;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }
}