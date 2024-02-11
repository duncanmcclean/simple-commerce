<?php

namespace DuncanMcClean\SimpleCommerce\Tax\Standard;

use DuncanMcClean\SimpleCommerce\Facades\TaxCategory as TaxCategoryFacade;
use DuncanMcClean\SimpleCommerce\Facades\TaxRate;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Facades\Stache;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class TaxCategory
{
    use ContainsData, ExistsAsFile, FluentlyGetsAndSets, TracksQueriedColumns;

    public $id;

    public $name;

    public $description;

    protected $selectedQueryRelations = [];

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

    public function description($description = null)
    {
        return $this
            ->fluentlyGetOrSet('description')
            ->args(func_get_args());
    }

    public function save()
    {
        TaxCategoryFacade::save($this);

        return true;
    }

    public function delete()
    {
        TaxRate::all()
            ->where('category', $this->id())
            ->each(function ($taxRate) {
                $taxRate->delete();
            });

        TaxCategoryFacade::delete($this);

        return true;
    }

    public function path()
    {
        return Stache::store('simple-commerce-tax-categories')->directory().$this->id().'.yaml';
    }

    public function fileData()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }

    public function editUrl()
    {
        return cp_route('simple-commerce.tax-categories.edit', [
            'taxCategory' => $this->id(),
        ]);
    }

    public function updateUrl()
    {
        return cp_route('simple-commerce.tax-categories.update', [
            'taxCategory' => $this->id(),
        ]);
    }

    public function deleteUrl()
    {
        return cp_route('simple-commerce.tax-categories.destroy', [
            'taxCategory' => $this->id(),
        ]);
    }

    public function selectedQueryRelations($relations)
    {
        $this->selectedQueryRelations = $relations;

        return $this;
    }
}
