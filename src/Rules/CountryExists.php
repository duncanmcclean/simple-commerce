<?php

namespace DuncanMcClean\SimpleCommerce\Rules;

use DuncanMcClean\SimpleCommerce\Countries;
use Illuminate\Contracts\Validation\Rule;

class CountryExists implements Rule
{
    public function passes($attribute, $value)
    {
        $matchesIso = collect(Countries::toArray())->filter(function ($country) use ($value) {
            return $country['iso'] == $value;
        });

        return $matchesIso->count() >= 1;
    }

    public function message()
    {
        return __('The selected country is not recognised.');
    }
}
