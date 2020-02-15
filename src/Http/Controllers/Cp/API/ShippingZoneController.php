<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp\API;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\ShippingZoneRequest;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingZone;
use Statamic\Http\Controllers\CP\CpController;

class ShippingZoneController extends CpController
{
    public function index()
    {
        return ShippingZone::with('country', 'state')->get();
    }

    public function store(ShippingZoneRequest $request): ShippingZone
    {
        $zone = new ShippingZone();
        $zone->country_id = $request->country[0];
        $zone->state_id = isset($request->state[0]) ?? null;
        $zone->start_of_zip_code = $request->start_of_zip_code;
        $zone->price = $request->price;
        $zone->save();

        return $zone;
    }

    public function update(ShippingZone $zone, ShippingZoneRequest $request): ShippingZone
    {
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

        return redirect(cp_route('settings.shipping-zones.index'))
            ->with('success', 'Deleted shipping zone');
    }
}
