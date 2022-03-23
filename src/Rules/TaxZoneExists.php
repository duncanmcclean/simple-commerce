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
        return __('simple-commerce::messages.validation.tax_zone_exists');
    }
}
