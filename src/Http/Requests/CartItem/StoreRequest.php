<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CartItem;

use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Http\Requests\AcceptsFormRequests;
use DuncanMcClean\SimpleCommerce\Orders\Order as EntryOrder;
use DuncanMcClean\SimpleCommerce\Products\ProductType;
use DuncanMcClean\SimpleCommerce\Rules\ProductExists;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    use AcceptsFormRequests;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'product' => [
                'required',
                'string',
            ],
            'variant' => [
                Rule::requiredIf(function () {
                    return Product::find($this->product)->purchasableType() === ProductType::Variant;
                }),
                'string',
            ],
            'quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'email' => [
                'nullable',
                'email',
                function ($attribute, $value, $fail) {
                    if (preg_match('/^\S*$/u', $value) === 0) {
                        return $fail(__('Your email may not contain any spaces.'));
                    }
                },
            ],
            'customer.email' => [
                'nullable',
                'email',
                function ($attribute, $value, $fail) {
                    if (preg_match('/^\S*$/u', $value) === 0) {
                        return $fail(__('Your email may not contain any spaces.'));
                    }
                },
            ],
        ];

        if ($formRequest = $this->get('_request')) {
            return array_merge(
                $rules,
                $this->buildFormRequest($formRequest, $this)->rules()
            );
        }

        if (SimpleCommerce::orderDriver() === EntryOrder::class) {
            $rules['product'][] = new ProductExists;
        }

        return $rules;
    }

    public function messages()
    {
        if ($formRequest = $this->get('_request')) {
            return $this->buildFormRequest($formRequest, $this)->messages();
        }

        return [];
    }
}
