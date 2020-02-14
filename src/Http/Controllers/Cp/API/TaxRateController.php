<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp\API;

use DoubleThreeDigital\SimpleCommerce\Models\TaxRate;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Stache\Stache;

class TaxRateController extends CpController
{
    public function index()
    {
        return TaxRate::with('country', 'state')->get();
    }

    public function store(Request $request)
    {
        // TODO: setup a validation request

        $rate = new TaxRate();
        $rate->uuid = (new Stache())->generateId();
        $rate->name = $request->name;
        $rate->country_id = $request->country[0];
        $rate->state_id = isset($request->state[0]) ?? null;
        $rate->start_of_zip_code = $request->start_of_zip_code;
        $rate->rate = $request->rate;
        $rate->save();

        return $rate;
    }

    public function update(TaxRate $rate, Request $request)
    {
        // TODO: setup a validation request

        $rate->name = $request->name;
        $rate->country_id = $request->country[0];
        $rate->state_id = isset($request->state[0]) ?? null;
        $rate->start_of_zip_code = $request->start_of_zip_code;
        $rate->rate = $request->rate;
        $rate->save();

        return $rate;
    }

    public function destroy(TaxRate $rate)
    {
        $rate->delete();

        return redirect(cp_route('settings.edit'))
            ->with('success', 'Deleted tax rate');
    }
}
