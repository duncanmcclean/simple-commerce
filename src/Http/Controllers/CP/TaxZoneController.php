<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP;

use DuncanMcClean\SimpleCommerce\Countries;
use DuncanMcClean\SimpleCommerce\Facades\TaxZone;
use DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxZone\CreateRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxZone\DeleteRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxZone\EditRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxZone\IndexRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxZone\StoreRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxZone\UpdateRequest;
use Statamic\Facades\Stache;

class TaxZoneController
{
    public function index(IndexRequest $request)
    {
        return view('simple-commerce::cp.tax-zones.index', [
            'taxZones' => TaxZone::all(),
        ]);
    }

    public function create(CreateRequest $request)
    {
        return view('simple-commerce::cp.tax-zones.create', [
            'countries' => Countries::sortBy('name')->all(),
        ]);
    }

    public function store(StoreRequest $request)
    {
        $taxZone = TaxZone::make()
            ->id(Stache::generateId())
            ->name($request->name)
            ->country($request->country);

        if ($request->region) {
            $taxZone->region($request->region);
        }

        $taxZone->save();

        return redirect(cp_route('simple-commerce.tax-zones.index'));
    }

    public function edit(EditRequest $request, $taxZone)
    {
        $taxZone = TaxZone::find($taxZone);

        return view('simple-commerce::cp.tax-zones.edit', [
            'taxZone' => $taxZone,
            'countries' => Countries::sortBy('name')->all(),
        ]);
    }

    public function update(UpdateRequest $request, $taxZone)
    {
        $taxZone = TaxZone::find($taxZone)
            ->name($request->name)
            ->country($request->country);

        if ($request->region) {
            $taxZone->region($request->region);
        }

        $taxZone->save();

        return redirect($taxZone->editUrl());
    }

    public function destroy(DeleteRequest $request, $taxZone)
    {
        TaxZone::find($taxZone)->delete();

        return [
            'success' => true,
        ];
    }
}
