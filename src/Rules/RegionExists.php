<?php

namespace DuncanMcClean\SimpleCommerce\Rules;

use DuncanMcClean\SimpleCommerce\Regions;
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
        return __('The selected region is not recognised.');
    }
}
