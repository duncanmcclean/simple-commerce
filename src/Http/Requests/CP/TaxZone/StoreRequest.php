<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\TaxZone;

use DoubleThreeDigital\SimpleCommerce\Support\Rules\CountryExists;
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
            'name' => ['required', 'string'],
            'country' => ['required', new CountryExists],
            'region' => ['nullable'], // TODO: RegionExists
        ];
    }
}
