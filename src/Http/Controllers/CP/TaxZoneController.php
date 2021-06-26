<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP;

use DoubleThreeDigital\SimpleCommerce\Facades\TaxZone;
use DoubleThreeDigital\SimpleCommerce\Support\Countries;
use DoubleThreeDigital\SimpleCommerce\Support\Regions;
use Illuminate\Http\Request;
use Statamic\Facades\Stache;

class TaxZoneController
{
    public function index()
    {
        return view('simple-commerce::cp.tax-zones.index', [
            'taxZones' => TaxZone::all(),
        ]);
    }

    public function create()
    {
        return view('simple-commerce::cp.tax-zones.create', [
            'countries' => Countries::all(),
            'regions' => Regions::all(),
        ]);
    }

    public function store(Request $request)
    {
        $taxZone = TaxZone::make()
            ->id(Stache::generateId())
            ->name($request->name)
            ->country($request->country);

        if ($request->region) {
            $taxZone->region($request->region);
        }

        $taxZone->save();

        return redirect($taxZone->editUrl());
    }

    public function edit(Request $request, $taxZone)
    {
        $taxZone = TaxZone::find($taxZone);

        return view('simple-commerce::cp.tax-zones.edit', [
            'taxZone' => $taxZone,
            'countries' => Countries::all(),
            'regions' => Regions::all(),
        ]);
    }

    public function update(Request $request, $taxZone)
    {
        $taxZone = TaxZone::make()
            ->id(Stache::generateId())
            ->name($request->name)
            ->country($request->country);

        if ($request->region) {
            $taxZone->region($request->region);
        }

        $taxZone->save();

        return redirect($taxZone->editUrl());
    }

    public function destroy(Request $request, $taxZone)
    {
        TaxZone::find($taxZone)->delete();

        return redirect(cp_route('simple-commerce.tax-zones.index'));
    }
}
