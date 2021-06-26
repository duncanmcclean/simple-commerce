<?php

namespace DoubleThreeDigital\SimpleCommerce\Tax\Standard;

use DoubleThreeDigital\SimpleCommerce\Facades\TaxCategory as TaxCategoryFacade;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxRate as TaxRateFacade;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxZone;
use DoubleThreeDigital\SimpleCommerce\Support\Countries;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Facades\Stache;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class TaxRate
{
    use FluentlyGetsAndSets, ExistsAsFile, TracksQueriedColumns, ContainsData;

    protected $id;
    protected $name;
    protected $rate;
    protected $category;
    protected $zone;

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
    }

    public function id($id = null)
    {
        return $this
            ->fluentlyGetOrSet('id')
            ->getter(function ($id) {
                if (! $id) {
                    return app('stache')->generateId();
                }

                return $id;
            })
            ->args(func_get_args());
    }

    public function name($name = null)
    {
        return $this
            ->fluentlyGetOrSet('name')
            ->args(func_get_args());
    }

    public function rate($rate = null)
    {
        return $this
            ->fluentlyGetOrSet('rate')
            ->args(func_get_args());
    }

    public function category($category = null)
    {
        return $this
            ->fluentlyGetOrSet('category')
            ->getter(function ($category) {
                if (! $category instanceof TaxCategory) {
                    return TaxCategoryFacade::find($category);
                }

                return $category;
            })
            ->args(func_get_args());
    }

    public function zone($zone = null)
    {
        return $this
            ->fluentlyGetOrSet('zone')
            ->getter(function ($zone) {
                return TaxZone::find($zone);
            })
            ->args(func_get_args());
    }

    public function save()
    {
        TaxRateFacade::save($this);

        return true;
    }

    public function delete()
    {
        TaxRateFacade::delete($this);

        return true;
    }

    public function path()
    {
        return Stache::store('simple-commerce-tax-rates')->directory() . $this->id() . '.yaml';
    }

    public function fileData()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'rate' => $this->rate,
            'category' => $this->category,
            'zone' => $this->zone,
        ];
    }

    public function editUrl()
    {
        return cp_route('simple-commerce.tax-rates.edit', [
            'taxRate' => $this->id(),
        ]);
    }

    public function updateUrl()
    {
        return cp_route('simple-commerce.tax-rates.update', [
            'taxRate' => $this->id(),
        ]);
    }

    public function deleteUrl()
    {
        return cp_route('simple-commerce.tax-rates.destroy', [
            'taxRate' => $this->id(),
        ]);
    }
}
