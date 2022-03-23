<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP;

use DoubleThreeDigital\SimpleCommerce\Countries;
use DoubleThreeDigital\SimpleCommerce\Regions;
use Illuminate\Http\Request;

class RegionController
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'country' => ['required', 'string', 'in:'.Countries::pluck('iso')->join(',')],
        ]);

        $country = Countries::firstWhere('iso', $request->input('country'));

        return Regions::findByCountry($country)
            ->sortBy('name')
            ->all();
    }
}
