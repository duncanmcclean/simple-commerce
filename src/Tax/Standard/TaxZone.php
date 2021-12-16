<?php

namespace DoubleThreeDigital\SimpleCommerce\Tax\Standard;

use DoubleThreeDigital\SimpleCommerce\Facades\TaxRate;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxZone as TaxZoneFacade;
use DoubleThreeDigital\SimpleCommerce\Support\Countries;
use DoubleThreeDigital\SimpleCommerce\Support\Regions;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Facades\Stache;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class TaxZone
{
    use FluentlyGetsAndSets;
    use ExistsAsFile;
    use TracksQueriedColumns;
    use ContainsData;

    public $id;
    public $name;
    public $country;
    public $region;

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

    public function country($country = null)
    {
        return $this
            ->fluentlyGetOrSet('country')
            ->getter(function ($country) {
                return Countries::firstWhere('iso', $country);
            })
            ->args(func_get_args());
    }

    public function region($region = null)
    {
        return $this
            ->fluentlyGetOrSet('region')
            ->getter(function ($region) {
                return Regions::firstWhere('id', $region);
            })
            ->args(func_get_args());
    }

    public function save()
    {
        TaxZoneFacade::save($this);

        return true;
    }

    public function delete()
    {
        TaxRate::all()
            ->where('zone', $this->id())
            ->each(function ($taxRate) {
                $taxRate->delete();
            });

        TaxZoneFacade::delete($this);

        return true;
    }

    public function path()
    {
        return Stache::store('simple-commerce-tax-zones')->directory().$this->id().'.yaml';
    }

    public function fileData()
    {
        return [
            'id'      => $this->id,
            'name'    => $this->name,
            'country' => $this->country,
            'region'  => $this->region,
        ];
    }

    public function editUrl()
    {
        return cp_route('simple-commerce.tax-zones.edit', [
            'taxZone' => $this->id(),
        ]);
    }

    public function updateUrl()
    {
        return cp_route('simple-commerce.tax-zones.update', [
            'taxZone' => $this->id(),
        ]);
    }

    public function deleteUrl()
    {
        return cp_route('simple-commerce.tax-zones.destroy', [
            'taxZone' => $this->id(),
        ]);
    }
}
