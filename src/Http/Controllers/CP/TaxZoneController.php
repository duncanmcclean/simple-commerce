<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP;

use DuncanMcClean\SimpleCommerce\Countries;
use DuncanMcClean\SimpleCommerce\Facades\TaxZone;
use DuncanMcClean\SimpleCommerce\Regions;
use DuncanMcClean\SimpleCommerce\Rules\CountryExists;
use DuncanMcClean\SimpleCommerce\Rules\RegionExists;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Http\Request;
use Statamic\CP\PublishForm;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Stache;

class TaxZoneController
{
    public function index(Request $request)
    {
        return view('simple-commerce::cp.tax-zones.index', [
            'taxZones' => TaxZone::all(),
        ]);
    }

    public function create(Request $request)
    {
        return PublishForm::make($this->blueprint())
            ->title('Create Tax Zone')
            ->icon(SimpleCommerce::svg('percentage'))
            ->submittingTo(cp_route('simple-commerce.tax-zones.store'), 'POST');
    }

    public function store(Request $request)
    {
        $values = PublishForm::make($this->blueprint())->submit($request->all());

        $request->validate([
            'country.0' => [
                'required',
                new CountryExists,
                function ($attribute, $value, $fail) use ($request) {
                    if (! $request->region) {
                        $taxZoneWithCountryAlreadyExists = TaxZone::all()
                            ->where('country', $value)
                            ->where('region', null)
                            ->count() > 0;

                        if ($taxZoneWithCountryAlreadyExists) {
                            $country = Countries::find($value);

                            $fail(__('There is already a tax zone for :country.', ['country' => $country['name']]));
                        }
                    }
                },
            ],
            'region' => [
                'nullable',
                new RegionExists,
                function ($attribute, $value, $fail) use ($request) {
                    $taxZoneWithCountryAndRegionAlreadyExists = TaxZone::all()
                        ->where('country', $request->country[0] ?? null)
                        ->where('region', $value)
                        ->count() > 0;

                    if ($taxZoneWithCountryAndRegionAlreadyExists) {
                        $country = Countries::find($request->country[0]);
                        $region = Regions::find($value);

                        $fail(__('There is already a tax zone for :region, :country.', [
                            'region' => $region['name'],
                            'country' => $country['name'],
                        ]));
                    }
                },
            ],
        ]);

        $taxZone = TaxZone::make()
            ->id(Stache::generateId())
            ->name($values['name'])
            ->country($values['country'] ? $values['country'][0] : null);

        if (isset($values['region']) && $values['region']) {
            $taxZone->region($values['region']);
        }

        $taxZone->save();

        return ['redirect' => redirect(cp_route('simple-commerce.tax-zones.index'))];
    }

    public function edit(Request $request, $taxZone)
    {
        $taxZone = TaxZone::find($taxZone);

        return PublishForm::make($this->blueprint())
            ->title('Edit Tax Zone')
            ->icon(SimpleCommerce::svg('percentage'))
            ->values([
                'name' => $taxZone->name(),
                'country' => $taxZone->country() ? $taxZone->country()['iso'] : null,
                'region' => $taxZone->region(),
            ])
            ->submittingTo($taxZone->updateUrl());
    }

    public function update(Request $request, $taxZone)
    {
        $values = PublishForm::make($this->blueprint())->submit($request->all());

        $request->validate([
            'country.0' => [
                'required',
                new CountryExists,
                function ($attribute, $value, $fail) use ($request) {
                    if (! $request->region) {
                        $taxZoneWithCountryAlreadyExists = TaxZone::all()
                            ->where('country', $value)
                            ->where('region', null)
                            ->reject(function ($taxZone) use ($request) {
                                return $taxZone->id() === $request->route('taxZone');
                            })
                            ->count() > 0;

                        if ($taxZoneWithCountryAlreadyExists) {
                            $country = Countries::find($value);

                            $fail(__('There is already a tax zone for :country.', ['country' => $country['name']]));
                        }
                    }
                },
            ],
            'region' => [
                'nullable',
                new RegionExists,
                function ($attribute, $value, $fail) use ($request) {
                    $taxZoneWithCountryAndRegionAlreadyExists = TaxZone::all()
                        ->where('country', $request->country[0] ?? null)
                        ->where('region', $value)
                        ->reject(function ($taxZone) use ($request) {
                            return $taxZone->id() === $request->route('taxZone');
                        })
                        ->count() > 0;

                    if ($taxZoneWithCountryAndRegionAlreadyExists) {
                        $country = Countries::find($request->country[0]);
                        $region = Regions::find($value);

                        $fail(__('There is already a tax zone for :region, :country.', [
                            'region' => $region['name'],
                            'country' => $country['name'],
                        ]));
                    }
                },
            ],
        ]);

        $taxZone = TaxZone::find($taxZone)
            ->name($values['name'])
            ->country($values['country'] ? $values['country'][0] : null);

        if (isset($values['region']) && $values['region']) {
            $taxZone->region($values['region']);
        }

        $taxZone->save();

        return [];
    }

    public function destroy(Request $request, $taxZone)
    {
        TaxZone::find($taxZone)->delete();

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
                    'handle' => 'country',
                    'field' => [
                        'type' => 'country',
                        'display' => __('Country'),
                        'validate' => 'required',
                        'max_items' => 1,
                        'mode' => 'select',
                        'unless' => [
                            'name' => 'equals Everywhere',
                        ],
                    ],
                ],
                [
                    'handle' => 'region',
                    'field' => [
                        'type' => 'country_region',
                        'unless' => [
                            'name' => 'equals Everywhere',
                        ],
                    ],
                ],
            ]]]]],
        ]);
    }
}
