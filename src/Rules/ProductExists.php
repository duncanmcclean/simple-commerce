<?php

namespace DuncanMcClean\SimpleCommerce\Rules;

use DuncanMcClean\SimpleCommerce\Facades\Product;
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
