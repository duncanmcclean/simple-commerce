<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP;

use DuncanMcClean\SimpleCommerce\Facades\TaxCategory;
use DuncanMcClean\SimpleCommerce\Facades\TaxRate;
use DuncanMcClean\SimpleCommerce\Facades\TaxZone;
use DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxRate\CreateRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxRate\DeleteRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxRate\EditRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxRate\IndexRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxRate\StoreRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxRate\UpdateRequest;
use Statamic\Facades\Stache;

class TaxRateController
{
    public function index(IndexRequest $request)
    {
        return view('simple-commerce::cp.tax-rates.index', [
            'taxRates' => TaxRate::all(),
            'taxCategories' => TaxCategory::all(),
        ]);
    }

    public function create(CreateRequest $request)
    {
        return view('simple-commerce::cp.tax-rates.create', [
            'taxCategory' => TaxCategory::find($request->taxCategory),
            'taxZones' => TaxZone::all(),
        ]);
    }

    public function store(StoreRequest $request)
    {
        $taxRate = TaxRate::make()
            ->id(Stache::generateId())
            ->name($request->name)
            ->rate($request->rate)
            ->category($request->category)
            ->zone($request->zone)
            ->includeInPrice($request->include_in_price);

        $taxRate->save();

        return redirect(cp_route('simple-commerce.tax-rates.index'));
    }

    public function edit(EditRequest $request, $taxRate)
    {
        $taxRate = TaxRate::find($taxRate);

        return view('simple-commerce::cp.tax-rates.edit', [
            'taxRate' => $taxRate,
            'taxCategories' => TaxCategory::all(),
            'taxZones' => TaxZone::all(),
        ]);
    }

    public function update(UpdateRequest $request, $taxRate)
    {
        $taxRate = TaxRate::find($taxRate)
            ->name($request->name)
            ->rate($request->rate)
            ->category($request->category)
            ->zone($request->zone)
            ->includeInPrice($request->include_in_price);

        $taxRate->save();

        return redirect($taxRate->editUrl());
    }

    public function destroy(DeleteRequest $request, $taxRate)
    {
        TaxRate::find($taxRate)->delete();

        return [
            'success' => true,
        ];
    }
}
