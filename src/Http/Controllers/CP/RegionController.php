<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP;

use DuncanMcClean\SimpleCommerce\Countries;
use DuncanMcClean\SimpleCommerce\Regions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegionController
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'country' => [
                'required',
                'string',
                Rule::in(Countries::pluck('iso')->toArray()),
            ],
        ]);

        $country = Countries::firstWhere('iso', $request->input('country'));

        return Regions::findByCountry($country)
            ->sortBy('name')
            ->all();
    }
}
