<?php

namespace DoubleThreeDigital\SimpleCommerce\Support\Rules;

use DoubleThreeDigital\SimpleCommerce\Data\Countries;
use Illuminate\Contracts\Validation\Rule;
use Statamic\Facades\Entry;
use Illuminate\Support\Str;

class CountryExists implements Rule
{
    public function passes($attribute, $value)
    {
        $matchesIso = collect(Countries::toArray())->filter(function ($country) use ($value) {
            return $country['iso'] == $value;
        });

        $matchesCountryNameAsSlug = collect(Countries::toArray())->filter(function ($country) use ($value) {
            return Str::slug($country['name']) == $value;
        });

        return $matchesIso
            ?? $matchesCountryNameAsSlug;
    }

    public function message()
    {
        return __('simple-commerce::validation.country_exists');
    }
}
