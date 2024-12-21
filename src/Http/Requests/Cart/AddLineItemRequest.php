<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\Cart;

use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Products\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddLineItemRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'product' => [
                'required',
                function ($attribute, $value, $fail) {
                    $product = Product::find($value);

                    if (! $product) {
                        return $fail(__('The product is invalid.'));
                    }

                    if (! in_array($product->collectionHandle(), config('statamic.simple-commerce.products.collections'))) {
                        $fail(__('The product is invalid.'));
                    }
                },
            ],
            'variant' => [
                Rule::requiredIf(fn () => Product::find($this->product)?->type() === ProductType::Variant),
                function ($attribute, $value, $fail) {
                    $product = Product::find($this->product);

                    if ($product->type() === ProductType::Variant) {
                        $variant = $product->variant($value);

                        if (! $variant) {
                            return $fail(__('The variant is invalid.'));
                        }
                    }

                },
            ],
            'quantity' => ['nullable', 'integer', 'gt:0'],
        ];
    }
}
