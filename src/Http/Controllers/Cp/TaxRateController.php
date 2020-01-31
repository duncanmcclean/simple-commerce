<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Models\TaxRate;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Stache\Stache;

class TaxRateController extends CpController
{
    public function index()
    {
        if (! auth()->user()->hasPermission('edit settings') && auth()->user()->isSuper() != true) {
            abort(401);
        }

        return TaxRate::with('country', 'state')->get();
    }

    public function store(Request $request)
    {
        if (! auth()->user()->hasPermission('edit settings') && auth()->user()->isSuper() != true) {
            abort(401);
        }

        // TODO: setup a validation request

        $rate = new TaxRate();
        $rate->uid = (new Stache())->generateId();
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
        if (! auth()->user()->hasPermission('edit settings') && auth()->user()->isSuper() != true) {
            abort(401);
        }

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
        if (! auth()->user()->hasPermission('edit settings') && auth()->user()->isSuper() != true) {
            abort(401);
        }

        $rate->delete();

        return redirect(cp_route('settings.edit'))
            ->with('success', 'Deleted tax rate');
    }
}
