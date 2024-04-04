<?php

namespace DuncanMcClean\SimpleCommerce\Tax\Standard\Stache\TaxCategory;

use DuncanMcClean\SimpleCommerce\Facades\TaxCategory;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;
use Statamic\Support\Arr;

class TaxCategoryStore extends BasicStore
{
    public function key()
    {
        return 'simple-commerce-tax-categories';
    }

    public function makeItemFromFile($path, $contents)
    {
        $data = YAML::file($path)->parse($contents);

        if (! $id = Arr::pull($data, 'id')) {
            $idGenerated = true;
            $id = app('stache')->generateId();
        }

        $taxCategory = TaxCategory::make()
            ->id($id)
            ->name(Arr::pull($data, 'name'));

        if (isset($data['description'])) {
            $taxCategory->description(Arr::pull($data, 'description'));
        }

        if (isset($idGenerated)) {
            $taxCategory->save();
        }

        return $taxCategory;
    }
}
