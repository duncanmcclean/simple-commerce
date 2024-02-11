<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\CP\TaxRate;

use DuncanMcClean\SimpleCommerce\Rules\TaxCategoryExists;
use DuncanMcClean\SimpleCommerce\Rules\TaxZoneExists;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('edit tax rates');
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string'],
            'rate' => ['required', 'numeric'],
            'category' => ['required', 'string', new TaxCategoryExists],
            'zone' => ['required', 'string', new TaxZoneExists],
            'include_in_price' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'include_in_price' => $this->include_in_price === 'true',
        ]);
    }
}
