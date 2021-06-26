<?php

namespace DoubleThreeDigital\SimpleCommerce\Tax\Standard\Stache\TaxZone;

use DoubleThreeDigital\SimpleCommerce\Facades\TaxZone;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;

class TaxZoneStore extends BasicStore
{
    public function key()
    {
        return 'simple-commerce-tax-zones';
    }

    public function makeItemFromFile($path, $contents)
    {
        $data = YAML::file($path)->parse($contents);

        if (! $id = array_pull($data, 'id')) {
            $idGenerated = true;
            $id = app('stache')->generateId();
        }

        $taxZone = TaxZone::make()
            ->id($id)
            ->name(array_pull($data, 'name'))
            ->country(array_pull($data, 'country'));

        if (isset($data['region'])) {
            $taxZone->region(array_pull($data, 'region'));
        }

        if (isset($idGenerated)) {
            $taxZone->save();
        }

        return $taxZone;
    }
}
