<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class DestroyRequest extends FormRequest
{
    protected $errorBag = 'simple-commerce';

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }
}
