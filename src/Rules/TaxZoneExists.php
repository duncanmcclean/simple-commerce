<?php

namespace DoubleThreeDigital\SimpleCommerce\Rules;

use DoubleThreeDigital\SimpleCommerce\Facades\TaxZone;
use Illuminate\Contracts\Validation\Rule;

class TaxZoneExists implements Rule
{
    public function passes($attribute, $value)
    {
        return TaxZone::find($value) !== null;
    }

    public function message()
    {
        return __('Sorry, the tax zone provided could not be found.');
    }
}
