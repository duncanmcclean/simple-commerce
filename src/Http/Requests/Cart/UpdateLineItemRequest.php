<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\Cart;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Products\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Statamic\Exceptions\NotFoundHttpException;

class UpdateLineItemRequest extends FormRequest
{
    public function authorize()
    {
        throw_if(! Cart::hasCurrentCart(), NotFoundHttpException::class);

        return true;
    }

    public function rules()
    {
        return [
            'variant' => [
                'nullable',
//                function ($attribute, $value, $fail) {
//                    $product = Product::find($this->product);
//
//                    if ($product->purchasableType() === ProductType::Variant) {
//                        $variant = $product->variant($value);
//
//                        if (! $variant) {
//                            return $fail(__('The variant is invalid.'));
//                        }
//                    }
//                }
            ],
            'quantity' => ['nullable', 'integer', 'gt:0'],
        ];
    }
}
