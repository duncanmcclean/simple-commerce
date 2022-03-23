<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\TaxZone;

use DoubleThreeDigital\SimpleCommerce\Facades\TaxZone;
use DoubleThreeDigital\SimpleCommerce\Support\Countries;
use DoubleThreeDigital\SimpleCommerce\Support\Regions;
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
        return [
            'name'    => ['required', 'string'],
            'country' => ['required', new CountryExists, function ($attribute, $value, $fail) {
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
            }],
            'region'  => ['nullable', new RegionExists, function ($attribute, $value, $fail) {
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
            }],
        ];
    }
}
