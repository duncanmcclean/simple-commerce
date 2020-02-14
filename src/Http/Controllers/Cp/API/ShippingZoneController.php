<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp\API;

use DoubleThreeDigital\SimpleCommerce\Models\ShippingZone;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Stache\Stache;

class ShippingZoneController extends CpController
{
    public function index()
    {
        return ShippingZone::with('country', 'state')->get();
    }

    public function store(Request $request)
    {
        // TODO: setup a validation request

        $zone = new ShippingZone();
        $zone->uuid = (new Stache())->generateId();
        $zone->country_id = $request->country[0];
        $zone->state_id = isset($request->state[0]) ?? null;
        $zone->start_of_zip_code = $request->start_of_zip_code;
        $zone->price = $request->price;
        $zone->save();

        return $zone;
    }

    public function update(ShippingZone $zone, Request $request)
    {
        // TODO: setup a validation request

        $zone->country_id = $request->country[0];
        $zone->state_id = isset($request->state[0]) ?? null;
        $zone->start_of_zip_code = $request->start_of_zip_code;
        $zone->price = $request->price;
        $zone->save();

        return $zone;
    }

    public function destroy(ShippingZone $zone)
    {
        $zone->delete();

        return redirect(cp_route('settings.edit'))
            ->with('success', 'Deleted shipping zone');
    }
}
