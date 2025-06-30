<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP;

use DuncanMcClean\SimpleCommerce\Facades\TaxCategory;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Http\Request;
use Statamic\CP\PublishForm;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Stache;

class TaxCategoryController
{
    public function index(Request $request)
    {
        return view('simple-commerce::cp.tax-categories.index', [
            'taxCategories' => TaxCategory::all(),
        ]);
    }

    public function create(Request $request)
    {
        return PublishForm::make($this->blueprint())
            ->title('Create Tax Category')
            ->icon(SimpleCommerce::svg('percentage'))
            ->submittingTo(cp_route('simple-commerce.tax-categories.store'), 'POST');
    }

    public function store(Request $request)
    {
        $values = PublishForm::make($this->blueprint())->submit($request->values);

        $taxCategory = TaxCategory::make()
            ->id(Stache::generateId())
            ->name($values['name'])
            ->description($values['description'] ?? null);

        $taxCategory->save();

        return ['redirect' => redirect(cp_route('simple-commerce.tax-categories.index'))];
    }

    public function edit(Request $request, $taxCategory)
    {
        $taxCategory = TaxCategory::find($taxCategory);

        return PublishForm::make($this->blueprint())
            ->title('Edit Tax Category')
            ->icon(SimpleCommerce::svg('percentage'))
            ->values([
                'name' => $taxCategory->name(),
                'description' => $taxCategory->description(),
            ])
            ->submittingTo($taxCategory->updateUrl());
    }

    public function update(Request $request, $taxCategory)
    {
        $values = PublishForm::make($this->blueprint())->submit($request->values);

        $taxCategory = TaxCategory::find($taxCategory)
            ->name($values['name'])
            ->description($values['description'] ?? null);

        $taxCategory->save();

        return [];
    }

    public function destroy(Request $request, $taxCategory)
    {
        TaxCategory::find($taxCategory)->delete();

        return [
            'success' => true,
        ];
    }

    private function blueprint()
    {
        return Blueprint::make('tax_category')->setContents([
            'tabs' => ['main' => ['sections' => [['fields' => [
                [
                    'handle' => 'name',
                    'field' => [
                        'type' => 'text',
                        'display' => __('Name'),
                        'validate' => 'required',
                    ],
                ],
                [
                    'handle' => 'description',
                    'field' => [
                        'type' => 'textarea',
                        'display' => __('Description'),
                    ],
                ],
            ]]]]],
        ]);
    }
}
