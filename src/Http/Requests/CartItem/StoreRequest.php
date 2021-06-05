<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\CartItem;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\HasValidFormParameters;
use DoubleThreeDigital\SimpleCommerce\Support\Rules\EntryExists;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    use HasValidFormParameters;

    public function authorize()
    {
        return $this->hasValidFormParameters();
    }

    public function rules()
    {
        return [
            'product'  => ['required', 'string', new EntryExists()],
            'variant'  => ['string'],
            'quantity' => ['required', 'numeric', 'gt:0'],
        ];
    }
}
