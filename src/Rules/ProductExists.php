<?php

namespace DoubleThreeDigital\SimpleCommerce\Rules;

use DoubleThreeDigital\SimpleCommerce\Exceptions\ProductNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use Illuminate\Contracts\Validation\Rule;

class ProductExists implements Rule
{
    public function passes($attribute, $value)
    {
        $product = Product::find($value);

        return ! is_null($product);
    }

    public function message()
    {
        return __('The product :value does not exist.');
    }
}
