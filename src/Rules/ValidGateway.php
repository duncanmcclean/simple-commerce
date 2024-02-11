<?php

namespace DuncanMcClean\SimpleCommerce\Rules;

use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Contracts\Validation\Rule;

class ValidGateway implements Rule
{
    public function passes($attribute, $value)
    {
        return SimpleCommerce::gateways()->where('handle', $value)->count() > 0;
    }

    public function message()
    {
        return __('The provided payment gateway is not valid.');
    }
}
