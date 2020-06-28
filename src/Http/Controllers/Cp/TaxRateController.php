<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\TaxRateRequest;
use DoubleThreeDigital\SimpleCommerce\Models\TaxRate;
use Statamic\Http\Controllers\CP\CpController;

class TaxRateController extends CpController
{
    public function index()
    {
        return TaxRate::get()->map(function (TaxRate $rate) {
            return array_merge($rate->toArray(), [
                'updateUrl' => $rate->updateUrl(),
                'deleteUrl' => $rate->deleteUrl(),
            ]);
        });
    }

    public function store(TaxRateRequest $request): TaxRate
    {
        return TaxRate::create([
            'name'              => $request->name,
            'rate'              => $request->rate,
            'description'       => $request->description,
        ]);
    }

    public function update(TaxRate $rate, TaxRateRequest $request): TaxRate
    {
        $rate->update([
            'name'              => $request->name,
            'rate'              => $request->rate,
            'description'       => $request->description,
        ]);

        return $rate->refresh();
    }

    public function destroy(TaxRate $rate)
    {
        $rate->delete();

        return redirect(cp_route('settings.tax-rates.index'))
            ->with('success', 'Deleted tax rate');
    }
}
