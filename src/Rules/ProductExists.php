<?php

namespace DoubleThreeDigital\SimpleCommerce\Rules;

use DoubleThreeDigital\SimpleCommerce\Exceptions\ProductNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use Illuminate\Contracts\Validation\Rule;

class ProductExists implements Rule
{
    public function passes($attribute, $value)
    {
        try {
            Product::find($value);

            return true;
        } catch (ProductNotFound $e) {
            return false;
        }
    }

    public function message()
    {
        return __('simple-commerce::messages.validation.entry_exists');
    }
}
