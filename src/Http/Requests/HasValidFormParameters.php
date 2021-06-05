<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests;

use DoubleThreeDigital\SimpleCommerce\Tags\Concerns\FormParameters;
use Illuminate\Http\Request;

trait HasValidFormParameters
{
    public function hasValidFormParameters(Request $request = null)
    {
        if (! $request) {
            $request = $this;
        }

        if (! $request->has('_params')) {
            throw new \Exception("Given form parameters are not valid.");
        }

        return FormParameters::check($request->get('_params'), $request->all());
    }
}
