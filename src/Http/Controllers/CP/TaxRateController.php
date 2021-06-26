<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP;

use DoubleThreeDigital\SimpleCommerce\Facades\TaxCategory;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxRate;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxZone;
use Illuminate\Http\Request;
use Statamic\Facades\Stache;

class TaxRateController
{
    public function index()
    {
        return view('simple-commerce::cp.tax-rates.index', [
            'taxRates' => TaxRate::all(),
            'taxCategories' => TaxCategory::all(),
        ]);
    }

    public function create()
    {
        return view('simple-commerce::cp.tax-rates.create', [
            'taxCategories' => TaxCategory::all(),
            'taxZones' => TaxZone::all(),
        ]);
    }

    public function store(Request $request)
    {
        $taxRate = TaxRate::make()
            ->id(Stache::generateId())
            ->name($request->name)
            ->rate($request->rate)
            ->category($request->category)
            ->zone($request->zone);

        $taxRate->save();

        return redirect($taxRate->editUrl());
    }

    public function edit(Request $request, $taxRate)
    {
        $taxRate = TaxRate::find($taxRate);

        return view('simple-commerce::cp.tax-rates.edit', [
            'taxRate' => $taxRate,
            'taxCategories' => TaxCategory::all(),
            'taxZones' => TaxZone::all(),
        ]);
    }

    public function update(Request $request, $taxRate)
    {
        $taxRate = TaxRate::find($taxRate)
            ->name($request->name)
            ->rate($request->rate)
            ->category($request->category)
            ->zone($request->zone);

        $taxRate->save();

        return redirect($taxRate->editUrl());
    }

    public function destroy(Request $request, $taxRate)
    {
        TaxRate::find($taxRate)->delete();

        return redirect(cp_route('simple-commerce.tax-rates.index'));
    }
}
