<?php

namespace DuncanMcClean\SimpleCommerce\Taxes;

use DuncanMcClean\SimpleCommerce\Contracts\Taxes\TaxClass as Contract;
use DuncanMcClean\SimpleCommerce\Events\TaxClassDeleted;
use DuncanMcClean\SimpleCommerce\Events\TaxClassSaved;
use DuncanMcClean\SimpleCommerce\Facades;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\ContainsData;
use Statamic\Data\HasAugmentedData;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class TaxClass implements Augmentable, Contract
{
    use ContainsData, FluentlyGetsAndSets, HasAugmentedData;

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
        Facades\TaxClass::save($this);

        event(new TaxClassSaved($this));

        return true;
    }

    public function delete(): bool
    {
        Facades\TaxClass::delete($this->handle());

        event(new TaxClassDeleted($this));

        return true;
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
