<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP;

use DuncanMcClean\SimpleCommerce\Facades\TaxCategory;
use DuncanMcClean\SimpleCommerce\Facades\TaxRate;
use DuncanMcClean\SimpleCommerce\Facades\TaxZone;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Statamic\CP\PublishForm;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Stache;

class TaxRateController
{
    public function index(Request $request)
    {
        return view('simple-commerce::cp.tax-rates.index', [
            'taxRates' => TaxRate::all(),
            'taxCategories' => TaxCategory::all(),
        ]);
    }

    public function create(Request $request)
    {
        return PublishForm::make($this->blueprint())
            ->title('Create Tax Rate')
            ->submittingTo(cp_route('simple-commerce.tax-rates.store'), 'POST');
    }

    public function store(Request $request)
    {
        $values = PublishForm::make($this->blueprint())->submit($request->values);

        $taxRate = TaxRate::make()
            ->id(Stache::generateId())
            ->name($values['name'])
            ->rate($values['rate'])
            ->category(Str::after('?taxCategory=', $request->headers->get('referer')))
            ->zone($values['zone'])
            ->includeInPrice($values['include_in_price']);

        $taxRate->save();

        return ['redirect' => cp_route('simple-commerce.tax-rates.edit', $taxRate->id())];
    }

    public function edit(Request $request, $taxRate)
    {
        $taxRate = TaxRate::find($taxRate);

        return PublishForm::make($this->blueprint())
            ->title('Edit Tax Rate')
            ->values([
                'name' => $taxRate->name(),
                'rate' => $taxRate->rate(),
                'zone' => $taxRate->zone()->id(),
                'include_in_price' => $taxRate->includeInPrice(),
            ])
            ->submittingTo($taxRate->updateUrl());
    }

    public function update(Request $request, $taxRate)
    {
        $values = PublishForm::make($this->blueprint())->submit($request->values);

        $taxRate = TaxRate::find($taxRate)
            ->name($values['name'])
            ->rate($values['rate'])
            ->zone($values['zone'])
            ->includeInPrice($values['include_in_price']);

        $taxRate->save();

        return [];
    }

    public function destroy(Request $request, $taxRate)
    {
        TaxRate::find($taxRate)->delete();

        return [
            'success' => true,
        ];
    }

    private function blueprint()
    {
        return Blueprint::make('tax_rate')->setContents([
            'tabs' => ['main' => ['sections' => [['fields' => [
                [
                    'handle' => 'name',
                    'field' => [
                        'type' => 'text',
                        'display' => __('Name'),
                        'validate' => 'required',
                        'width' => 50,
                    ],
                ],
                [
                    'handle' => 'rate',
                    'field' => [
                        'type' => 'text',
                        'display' => __('Rate'),
                        'validate' => 'required|numeric',
                        'width' => 50,
                        'append' => '%',
                    ],
                ],
                [
                    'handle' => 'zone',
                    'field' => [
                        'type' => 'select',
                        'display' => __('Zone'),
                        'options' => TaxZone::all()->mapWithKeys(fn ($zone) => [$zone->id() => $zone->name()]),
                        'validate' => 'required',
                    ],
                ],
                [
                    'handle' => 'include_in_price',
                    'field' => [
                        'type' => 'toggle',
                        'display' => __('Include in Price'),
                        'default' => false,
                    ],
                ],
            ]]]]],
        ]);
    }
}
