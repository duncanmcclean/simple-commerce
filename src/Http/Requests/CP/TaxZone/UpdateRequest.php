<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\TaxZone;

use DoubleThreeDigital\SimpleCommerce\Support\Rules\CountryExists;
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
            'country' => ['required', new CountryExists],
            'region'  => ['nullable'], // TODO: RegionExists
        ];
    }
}
