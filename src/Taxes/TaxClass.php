<?php

namespace DuncanMcClean\SimpleCommerce\Taxes;

use DuncanMcClean\SimpleCommerce\Facades;
use DuncanMcClean\SimpleCommerce\Contracts\Taxes\TaxClass as Contract;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\ContainsData;
use Statamic\Data\HasAugmentedData;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class TaxClass implements Contract, Augmentable
{
    use FluentlyGetsAndSets, ContainsData, HasAugmentedData;

    public $handle;

    public function __construct()
    {
        $this->data = collect();
    }

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    public function save(): bool
    {
        return Facades\TaxClass::save($this);
    }

    public function delete(): bool
    {
        return Facades\TaxClass::delete($this->handle());
    }

    public function editUrl()
    {
        return cp_route('simple-commerce.tax-classes.edit', $this->handle());
    }

    public function updateUrl()
    {
        return cp_route('simple-commerce.tax-classes.update', $this->handle());
    }

    public function deleteUrl()
    {
        return cp_route('simple-commerce.tax-classes.destroy', $this->handle());
    }

    public function toArray(): array
    {
        return $this->data()->merge(['handle' => $this->handle()])->all();
    }

    public function fileData(): array
    {
        return $this->data()->filter()->all();
    }

    public function augmentedArrayData()
    {
        return $this->toArray();
    }
}