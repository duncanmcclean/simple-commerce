<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\CP;

use Illuminate\Foundation\Http\FormRequest;

class OverviewRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('view simple commerce overview');
    }

    public function rules()
    {
        return [];
    }
}
