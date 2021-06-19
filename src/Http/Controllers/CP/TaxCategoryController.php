<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP;

use DoubleThreeDigital\SimpleCommerce\Facades\TaxCategory;
use Illuminate\Http\Request;
use Statamic\Facades\Stache;

class TaxCategoryController
{
    public function index()
    {
        return view('simple-commerce::cp.tax-categories.index', [
            'taxCategories' => TaxCategory::all(),
        ]);
    }

    public function create()
    {
        return view('simple-commerce::cp.tax-categories.create');
    }

    public function store(Request $request)
    {
        $taxCategory = TaxCategory::make()
            ->id(Stache::generateId())
            ->name($request->name)
            ->description($request->description);

        $taxCategory->save();

        return redirect($taxCategory->editUrl());
    }

    public function edit(Request $request, $taxCategory)
    {
        $taxCategory = TaxCategory::find($taxCategory);

        return view('simple-commerce::cp.tax-categories.edit', [
            'taxCategory' => $taxCategory,
        ]);
    }

    public function update(Request $request, $taxCategory)
    {
        $taxCategory = TaxCategory::find($taxCategory)
            ->name($request->name)
            ->description($request->description);

        $taxCategory->save();

        return redirect($taxCategory->editUrl());
    }

    public function destroy(Request $request, $taxCategory)
    {
        TaxCategory::find($taxCategory)->delete();

        return redirect(cp_route('simple-commerce.tax-categories.index'));
    }
}
