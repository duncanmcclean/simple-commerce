<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\TaxZone;

use DoubleThreeDigital\SimpleCommerce\Countries;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxZone;
use DoubleThreeDigital\SimpleCommerce\Regions;
use DoubleThreeDigital\SimpleCommerce\Rules\CountryExists;
use DoubleThreeDigital\SimpleCommerce\Rules\RegionExists;
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
            'name'    => ['required', 'string'],
        ];

        if ($this->taxZone !== 'everywhere') {
            $rules['country'] = ['required', new CountryExists, function ($attribute, $value, $fail) {
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

                        $fail("There is already a tax zone for {$country['name']}");
                    }
                }
            }];

            $rules['region'] = ['nullable', new RegionExists, function ($attribute, $value, $fail) {
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

                    $fail("There is already a tax zone for {$region['name']}, {$country['name']}");
                }
            }];
        }

        return $rules;
    }
}
