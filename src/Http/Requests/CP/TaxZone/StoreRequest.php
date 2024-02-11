<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxZone;

use DuncanMcClean\SimpleCommerce\Countries;
use DuncanMcClean\SimpleCommerce\Facades\TaxZone;
use DuncanMcClean\SimpleCommerce\Regions;
use DuncanMcClean\SimpleCommerce\Rules\CountryExists;
use DuncanMcClean\SimpleCommerce\Rules\RegionExists;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create tax zones');
    }

    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
            ],
            'country' => [
                'required',
                new CountryExists,
                function ($attribute, $value, $fail) {
                    if ($this->region === null) {
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
                function ($attribute, $value, $fail) {
                    $taxZoneWithCountryAndRegionAlreadyExists = TaxZone::all()
                        ->where('country', $this->country)
                        ->where('region', $value)
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
            ],
        ];
    }
}
