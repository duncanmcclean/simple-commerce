<?php

namespace DoubleThreeDigital\SimpleCommerce\Tax\Standard\Stache\TaxCategory;

use DoubleThreeDigital\SimpleCommerce\Facades\TaxCategory;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;

class TaxCategoryStore extends BasicStore
{
    public function key()
    {
        return 'simple-commerce-tax-categories';
    }

    public function makeItemFromFile($path, $contents)
    {
        $data = YAML::file($path)->parse($contents);

        if (! $id = array_pull($data, 'id')) {
            $idGenerated = true;
            $id = app('stache')->generateId();
        }

        $taxCategory = TaxCategory::make()
            ->id($id)
            ->name(array_pull($data, 'name'));

        if (isset($data['description'])) {
            $taxCategory->description(array_pull($data, 'description'));
        }

        if (isset($idGenerated)) {
            $taxCategory->save();
        }

        return $taxCategory;
    }
}
