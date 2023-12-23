<?php

namespace DoubleThreeDigital\SimpleCommerce\Rules;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use Illuminate\Contracts\Validation\Rule;

class ValidGateway implements Rule
{
    public function passes($attribute, $value)
    {
        if (! class_exists($value)) {
            return false;
        }

        $isGateway = (new $value()) instanceof Gateway;

        if (! $isGateway) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return __('The provided payment gateway is not valid.');
    }
}
