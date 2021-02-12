<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\CartItem;

use DoubleThreeDigital\SimpleCommerce\Support\Rules\EntryExists;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'product' => ['required', 'string', new EntryExists],
            'variant' => ['string'],
            'quantity' => ['required', 'numeric'],
        ];
    }
}
