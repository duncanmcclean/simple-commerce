<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

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
        return ShippingZone::create([
            'country_id'        => $request->country[0],
            'state_id'          => $request->state[0] ?? null,
            'start_of_zip_code' => $request->start_of_zip_code,
            'price'             => $request->price,
        ]);
    }

    public function update(ShippingZone $zone, ShippingZoneRequest $request): ShippingZone
    {
        $zone->update([
            'country_id'        => $request->country[0],
            'state_id'          => $request->state[0] ?? null,
            'start_of_zip_code' => $request->start_of_zip_code,
            'price'             => $request->price,
        ]);

        return $zone->refresh();
    }

    public function destroy(ShippingZone $zone)
    {
        $zone->delete();

        return redirect(cp_route('settings.shipping-zones.index'))->with('success', 'Deleted shipping zone');
    }
}
