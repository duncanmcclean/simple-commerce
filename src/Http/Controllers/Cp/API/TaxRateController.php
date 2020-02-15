<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp\API;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\TaxRateRequest;
use DoubleThreeDigital\SimpleCommerce\Models\TaxRate;
use Statamic\Http\Controllers\CP\CpController;

class TaxRateController extends CpController
{
    public function index()
    {
        return TaxRate::with('country', 'state')->get();
    }

    public function store(TaxRateRequest $request): TaxRate
    {
        $rate = new TaxRate();
        $rate->name = $request->name;
        $rate->country_id = $request->country[0];
        $rate->state_id = isset($request->state[0]) ?? null;
        $rate->start_of_zip_code = $request->start_of_zip_code;
        $rate->rate = $request->rate;
        $rate->save();

        return $rate;
    }

    public function update(TaxRate $rate, TaxRateRequest $request): TaxRate
    {
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

        return redirect(cp_route('settings.tax-rates.index'))
            ->with('success', 'Deleted tax rate');
    }
}
