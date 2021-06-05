<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\CartItem;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\HasValidFormParameters;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    use HasValidFormParameters;

    public function authorize()
    {
        return $this->hasValidFormParameters();
    }

    public function rules()
    {
        return [
            'quantity' => ['sometimes', 'numeric', 'gt:0'],
        ];
    }
}
