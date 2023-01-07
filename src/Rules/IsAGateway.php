<?php

namespace DoubleThreeDigital\SimpleCommerce\Rules;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use Illuminate\Contracts\Validation\Rule;

class IsAGateway implements Rule
{
    public function passes($attribute, $value)
    {
        if (! class_exists($value)) {
            return false;
        }

        return (new $value()) instanceof Gateway;
    }

    public function message()
    {
        return __(':value is not a gateway');
    }
}
