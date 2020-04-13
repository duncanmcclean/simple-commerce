<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

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
        return TaxRate::create([
            'name'              => $request->name,
            'country_id'        => $request->country[0],
            'state_id'          => $request->state[0] ?? null,
            'start_of_zip_code' => $request->start_of_zip_code,
            'rate'              => $request->rate,
        ]);
    }

    public function update(TaxRate $rate, TaxRateRequest $request): TaxRate
    {
        $rate->update([
            'name'              => $request->name,
            'country_id'        => $request->country[0],
            'state_id'          => $request->state[0] ?? null,
            'start_of_zip_code' => $request->start_of_zip_code,
            'rate'              => $request->rate,
        ]);

        return $rate->refresh();
    }

    public function destroy(TaxRate $rate)
    {
        $rate->delete();

        return redirect(cp_route('settings.tax-rates.index'))->with('success', 'Deleted tax rate');
    }
}
