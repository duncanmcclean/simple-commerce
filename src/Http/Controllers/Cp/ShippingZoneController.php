<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\ShippingZoneRequest;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingRate;
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
                    'editUrl' => $zone->editUrl(),
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
                ]);
            });

        return $zone->refresh();
    }

    public function edit(ShippingZone $zone)
    {
        $values = $zone->toArray();

        collect($zone->countries)
            ->each(function ($country) use (&$values) {
                $values['countries'][] = $country->id;
            });

        collect($zone->rates)
            ->each(function ($rate) use (&$values) {
                $values['rates'][] = $rate->toArray();
            });

        $blueprint = $zone->blueprint();
        $fields = $blueprint->fields()->addValues($values)->preProcess();

        return [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
            'action'    => $zone->updateUrl(),
        ];
    }

    public function update(ShippingZone $zone, ShippingZoneRequest $request): ShippingZone
    {
        $zone->update(['name' => $request->name]);

        $this->updateCountries($request, $zone);
        $this->updateRates($request, $zone);

        return $zone->refresh();
    }

    public function destroy(ShippingZone $zone)
    {
        $zone->countries()->get()->each(function ($country) {
            $country->update(['shipping_zone_id' => 0]);
        });

        $zone->rates()->get()->each(function ($zone) {
            $zone->delete();
        });

        $zone->delete();
    }

    protected function updateCountries($request, $zone)
    {
        $existingCountries = $zone->countries->pluck('id')->toArray(); // an array, the values of each item are country IDs
        $requestCountries = $request->countries;

        // Deal with removing countries
        collect(array_diff($existingCountries, $requestCountries))
            ->each(function ($countryId) {
                Country::find($countryId)
                    ->update(['shipping_zone_id' => 0]);
            });

        // And... deal with adding new countries
        collect(array_diff($requestCountries, $existingCountries))
            ->each(function ($countryId) use ($zone) {
                Country::find($countryId)
                    ->update(['shipping_zone_id' => $zone->id]);
            });
    }

    protected function updateRates($request, $zone)
    {
        // Deal with removing rates
        $requestRates = collect($request->rates)->reject(function ($rate) {
            return ! isset($rate['uuid']);
        })->map(function ($rate) {
            return ShippingRate::where('uuid', $rate['uuid'])->first()->id;
        })->toArray();

        collect(array_diff($zone->rates()->pluck('id')->toArray(), $requestRates))
            ->each(function ($rateId) {
                ShippingRate::find($rateId)->delete();
            });

        // Deal with creates and updates
        collect($request->rates)
            ->each(function ($rate) use ($zone) {
                if (! isset($rate['uuid'])) {
                    $zone->rates()->create([
                        'uuid'      => (new Stache())->generateId(),
                        'name'      => $rate['name'],
                        'type'      => $rate['type'],
                        'minimum'   => $rate['minimum'],
                        'maximum'   => $rate['maximum'],
                        'rate'      => $rate['rate'],
                    ]);
                } else {
                    $zone
                        ->rates()
                        ->where('uuid', $rate['uuid'])
                        ->first()
                        ->update([
                            'name'      => $rate['name'],
                            'type'      => $rate['type'],
                            'minimum'   => $rate['minimum'],
                            'maximum'   => $rate['maximum'],
                            'rate'      => $rate['rate'],
                        ]);
                }
            });
    }
}
