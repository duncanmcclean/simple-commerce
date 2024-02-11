<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CartItem;

use Illuminate\Foundation\Http\FormRequest;

class DestroyRequest extends FormRequest
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
