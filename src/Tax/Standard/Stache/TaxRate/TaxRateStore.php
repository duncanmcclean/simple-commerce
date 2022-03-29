<?php

namespace DoubleThreeDigital\SimpleCommerce\Tax\Standard\Stache\TaxRate;

use DoubleThreeDigital\SimpleCommerce\Facades\TaxRate;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;

class TaxRateStore extends BasicStore
{
    public function key()
    {
        return 'simple-commerce-tax-rates';
    }

    public function makeItemFromFile($path, $contents)
    {
        $data = YAML::file($path)->parse($contents);

        if (! $id = array_pull($data, 'id')) {
            $idGenerated = true;
            $id = app('stache')->generateId();
        }

        $taxCategory = TaxRate::make()
            ->id($id)
            ->name(array_pull($data, 'name'))
            ->rate(array_pull($data, 'rate'))
            ->category(array_pull($data, 'category'))
            ->zone(array_pull($data, 'zone'))
            ->includeInPrice(array_pull($data, 'include_in_price'));

        if (isset($idGenerated)) {
            $taxCategory->save();
        }

        return $taxCategory;
    }
}
