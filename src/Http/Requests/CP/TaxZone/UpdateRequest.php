<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxZone;

use DuncanMcClean\SimpleCommerce\Countries;
use DuncanMcClean\SimpleCommerce\Facades\TaxZone;
use DuncanMcClean\SimpleCommerce\Regions;
use DuncanMcClean\SimpleCommerce\Rules\CountryExists;
use DuncanMcClean\SimpleCommerce\Rules\RegionExists;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('edit tax zones');
    }

    public function rules()
    {
        $rules = [
            'name' => [
                'required',
                'string',
            ],
        ];

        if ($this->taxZone !== 'everywhere') {
            $rules['country'] = [
                'required',
                new CountryExists,
                function ($attribute, $value, $fail) {
                    if ($this->region === null) {
                        $taxZoneWithCountryAlreadyExists = TaxZone::all()
                            ->where('country', $value)
                            ->where('region', null)
                            ->reject(function ($taxZone) {
                                return $taxZone->id() === $this->route('taxZone');
                            })
                            ->count() > 0;

                        if ($taxZoneWithCountryAlreadyExists) {
                            $country = Countries::find($value);

                            $fail(__('There is already a tax zone for :country.', ['country' => $country['name']]));
                        }
                    }
                },
            ];

            $rules['region'] = [
                'nullable',
                new RegionExists,
                function ($attribute, $value, $fail) {
                    $taxZoneWithCountryAndRegionAlreadyExists = TaxZone::all()
                        ->where('country', $this->country)
                        ->where('region', $value)
                        ->reject(function ($taxZone) {
                            return $taxZone->id() === $this->route('taxZone');
                        })
                        ->count() > 0;

                    if ($taxZoneWithCountryAndRegionAlreadyExists) {
                        $country = Countries::find($this->country);
                        $region = Regions::find($value);

                        $fail(__('There is already a tax zone for :region, :country.', [
                            'region' => $region['name'],
                            'country' => $country['name'],
                        ]));
                    }
                },
            ];
        }

        return $rules;
    }
}
