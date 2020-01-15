<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string',
            'slug' => 'required|string',
            'description' => '',
            'category' => ''
        ];
    }
}
