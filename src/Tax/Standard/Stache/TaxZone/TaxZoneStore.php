<?php

namespace DuncanMcClean\SimpleCommerce\Tax\Standard\Stache\TaxZone;

use DuncanMcClean\SimpleCommerce\Facades\TaxZone;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;
use Statamic\Support\Arr;

class TaxZoneStore extends BasicStore
{
    public function key()
    {
        return 'simple-commerce-tax-zones';
    }

    public function makeItemFromFile($path, $contents)
    {
        $data = YAML::file($path)->parse($contents);

        if (! $id = Arr::pull($data, 'id')) {
            $idGenerated = true;
            $id = app('stache')->generateId();
        }

        $taxZone = TaxZone::make()
            ->id($id)
            ->name(Arr::pull($data, 'name'))
            ->country(Arr::pull($data, 'country'));

        if (isset($data['region'])) {
            $taxZone->region(Arr::pull($data, 'region'));
        }

        if (isset($idGenerated)) {
            $taxZone->save();
        }

        return $taxZone;
    }
}
