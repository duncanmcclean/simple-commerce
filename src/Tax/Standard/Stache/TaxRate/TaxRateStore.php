<?php

namespace DuncanMcClean\SimpleCommerce\Tax\Standard\Stache\TaxRate;

use DuncanMcClean\SimpleCommerce\Facades\TaxRate;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;
use Statamic\Support\Arr;

class TaxRateStore extends BasicStore
{
    public function key()
    {
        return 'simple-commerce-tax-rates';
    }

    public function makeItemFromFile($path, $contents)
    {
        $data = YAML::file($path)->parse($contents);

        if (! $id = Arr::pull($data, 'id')) {
            $idGenerated = true;
            $id = app('stache')->generateId();
        }

        $taxCategory = TaxRate::make()
            ->id($id)
            ->name(Arr::pull($data, 'name'))
            ->rate(Arr::pull($data, 'rate'))
            ->category(Arr::pull($data, 'category'))
            ->zone(Arr::pull($data, 'zone'))
            ->includeInPrice(Arr::pull($data, 'include_in_price'));

        if (isset($idGenerated)) {
            $taxCategory->save();
        }

        return $taxCategory;
    }
}
