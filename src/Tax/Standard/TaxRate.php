<?php

namespace DoubleThreeDigital\SimpleCommerce\Tax\Standard;

use DoubleThreeDigital\SimpleCommerce\Facades\TaxRate as TaxRateFacade;
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
    protected $country;
    protected $state;

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
    }

    public function id($id = null)
    {
        return $this
            ->fluentlyGetOrSet('id')
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

    public function country($country = null)
    {
        return $this
            ->fluentlyGetOrSet('rate')
            ->args(func_get_args());
    }

    public function state($state = null)
    {
        return $this
            ->fluentlyGetOrSet('state')
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
            'country' => $this->country,
            'state' => $this->state,
        ];
    }
}
