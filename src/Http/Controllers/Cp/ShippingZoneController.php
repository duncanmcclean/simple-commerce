<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\ShippingZoneRequest;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingZone;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Stache\Stache;

class ShippingZoneController extends CpController
{
    public function index()
    {
        return ShippingZone::with('countries')
            ->get()
            ->map(function (ShippingZone $zone) {
                return array_merge($zone->toArray(), [
                    'countries' => $zone->countries()
                        ->select('id', 'name')
                        ->get()
                        ->map(function ($country) {
                            return $country->id;
                        })
                        ->flatten()
                        ->toArray(),
                    'rates' => $zone->rates->toArray(),
                    'updateUrl' => $zone->updateUrl(),
                    'deleteUrl' => $zone->deleteUrl(),
                ]);
            })
            ->toArray();
    }

    public function store(ShippingZoneRequest $request): ShippingZone
    {
        $zone = ShippingZone::create([
            'uuid' => (new Stache())->generateId(),
            'name' => $request->name,
        ]);

        collect($request->countries)
            ->each(function ($country) use ($zone) {
                Country::find($country)->update([
                    'shipping_zone_id' => $zone->id,
                ]);
            });

        collect($request->rates)
            ->each(function ($rate) use ($zone) {
                $zone->rates()->create([
                    'uuid'      => (new Stache())->generateId(),
                    'name'      => $rate['name'],
                    'type'      => $rate['type'],
                    'minimum'   => $rate['minimum'],
                    'maximum'   => $rate['maximum'],
                    'rate'      => $rate['rate'],
                    'note'      => $rate['note'],
                ]);
            });

        return $zone->refresh();
    }

    public function update(ShippingZone $zone, ShippingZoneRequest $request): ShippingZone
    {
        $zone = $zone->update([
            'name' => $request->name,
        ]);

        collect($request->countries)
            ->each(function ($country) use ($zone) {
                if (! $zone->countries()->find($country)) {
                    Country::find($country)->update([
                        'shipping_zone_id' => $zone->id,
                    ]);
                }

                // TODO: figure out a good way of dealing with the situation if a country is removed by the user in the CP
            });

        collect($request->rates)
            ->each(function ($rate) use ($zone) {
                if (! is_null($rate->uuid)) {
                    $zone->rates()->create([
                        'uuid'      => (new Stache())->generateId(),
                        'name'      => $rate['name'],
                        'type'      => $rate['type'],
                        'minimum'   => $rate['minimum'],
                        'maximum'   => $rate['maximum'],
                        'rate'      => $rate['rate'],
                        'note'      => $rate['note'],
                    ]);
                } else {
                    $zone
                        ->rates()
                        ->where('uuid', $rate->uuid)
                        ->first()
                        ->update([
                            'name'      => $rate['name'],
                            'type'      => $rate['type'],
                            'minimum'   => $rate['minimum'],
                            'maximum'   => $rate['maximum'],
                            'rate'      => $rate['rate'],
                            'note'      => $rate['note'],
                        ]);
                }

                // TODO: figure out a good way of dealing with if a rate is removed by the user in the CP
            });

        return $zone->refresh();
    }

    public function destroy(ShippingZone $zone)
    {
        Country::where('shipping_zone_id', $zone->id)
            ->get()
            ->each(function ($country) {
                $country->update([
                    'shipping_zone_id' => null,
                ]);
            });

        $zone
            ->rates()
            ->get()
            ->each(function ($zone) {
                $zone->delete();
            });

        $zone->delete();
    }
}
