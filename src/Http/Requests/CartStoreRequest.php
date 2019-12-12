<?php

namespace Damcclean\Commerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'slug' => 'required|string',
            'quantity' => 'required|integer',
        ];
    }
}
