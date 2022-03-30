<?php

namespace DoubleThreeDigital\SimpleCommerce\Rules;

use DoubleThreeDigital\SimpleCommerce\Regions;
use Illuminate\Contracts\Validation\Rule;

class RegionExists implements Rule
{
    public function passes($attribute, $value)
    {
        $matchesId = collect(Regions::toArray())->filter(function ($region) use ($value) {
            return $region['id'] == $value;
        });

        return $matchesId->count() >= 1;
    }

    public function message()
    {
        return __('simple-commerce::messages.validation.region_exists');
    }
}
